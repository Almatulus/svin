<?php

namespace frontend\controllers;

use core\forms\order\OrderCreateForm;
use core\forms\order\OrderMoveForm;
use core\forms\order\OrderUpdateForm;
use core\forms\order\PendingOrderForm;
use core\helpers\order\OrderConstants;
use core\helpers\OrderHistoryHelper;
use core\helpers\TimetableHelper;
use core\models\company\Company;
use core\models\company\query\CompanyPositionQuery;
use core\models\customer\CompanyCustomer;
use core\models\order\Order;
use core\models\order\OrderService;
use core\models\Staff;
use core\models\user\User;
use core\repositories\exceptions\InsufficientStockLevel;
use core\services\dto\CustomerData;
use core\services\order\dto\OrderContactData;
use core\services\order\dto\OrderData;
use core\services\order\dto\OrderPaymentData;
use core\services\order\dto\OrderServiceData;
use core\services\order\dto\ProductData;
use core\services\order\OrderModelService;
use core\services\order\OrderStorageService;
use DateTime;
use frontend\modules\order\search\TimetableOrderSearch;
use Yii;
use yii\base\InvalidValueException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\Json;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\UploadedFile;

class TimetableController extends \yii\web\Controller
{
    private $orderService;
    private $orderStorageService;

    public function __construct(
        $id,
        $module,
        OrderModelService $orderService,
        OrderStorageService $orderStorageService,
        $config = []
    ) {
        $this->orderService        = $orderService;
        $this->orderStorageService = $orderStorageService;
        parent::__construct($id, $module, $config = []);
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['access'] = [
            'class' => AccessControl::className(),
            'rules' => [
                [
                    'allow'   => true,
                    'actions' => [
                        'update-event',
                        'update-event-duration',
                        'update-event-drop',
                        'delete-event',
                        'cancel-event',
                        'active-staff',
                        'checkout-event',
                        'return-event',
                        'delete-file',
                        'upload-file',
                        'add-pending',
                        'update-pending',
                        'enable-pending',
                        'delete-pending',
                        'index',
                        'history',
                        'search',
                        'events',
                        'add-event', // ToDo Temporary solution
                        'working-period'
                    ],
                    'roles'   => ['@'],
                ],
                [
                    'allow' => false,
                    'roles' => ['*'],
                ]
            ]
        ];

        $behaviors['verbs'] = [
            'class'   => VerbFilter::className(),
            'actions' => [
                'add-event'             => ['post'],
                'event-event'           => ['post'],
                'delete-event'          => ['post'],
                'update-event-duration' => ['post'],
                'update-event-drop'     => ['post'],
                'events'                => ['post'],
                'checkout-event'        => ['post'],
                'upload-file'           => ['post'],
                'add-pending'           => ['post'],
                'update-pending'        => ['post'],
                'enable-pending'        => ['post'],
                'delete-pending'        => ['post'],
            ],
        ];

        return $behaviors;
    }

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        if ( ! Yii::$app->user->isGuest) {
            /* @var Company $company */
            $company = Yii::$app->user->identity->company;
            $should_goto_new_design = $company->show_new_interface && ! empty(Yii::$app->params['vue_host']);
            if ($should_goto_new_design) {
                return $this->gotoNewDesign($company);
            }
        }

        return parent::beforeAction($action);
    }

    /**
     * @return mixed
     */
    public function actionIndex()
    {
        $staffQuery
            = Staff::find()
                   ->company()
                   ->valid()
                   ->orderBy('id');

        $selectedStaffQuery
            = Staff::find()
                   ->company()
                   ->valid()
                   ->withSchedule(new DateTime());

        /* @var User $user */
        $user  = Yii::$app->user->identity;
        $staff = $user->staff;
        if ($staff && $staff->see_own_orders) {
            $staffQuery->andWhere(['{{%staffs}}.id' => $staff->id]);
            $selectedStaffQuery->andWhere(['{{%staffs}}.id' => $staff->id]);
        }
        $staffs_selected = $selectedStaffQuery->all();
        $staffs          = $staffQuery->all();

        return $this->render(
            'index', [
                'staffs'          => $staffs,
                'staffs_selected' => $staffs_selected,
                'staff' => $staff,
                'duration'        => $user->company->getWorkingPeriod()
            ]
        );
    }

    /**
     * Returns events list
     */
    public function actionEvents()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        /* @var User $user */
        $user                    = Yii::$app->user->identity;
        $searchModel             = new TimetableOrderSearch();
        $searchModel->company_id = $user->company_id;
        $searchModel->load(Yii::$app->request->bodyParams);

        if ( ! $searchModel->validate()) {
            $error = $searchModel->getErrors();
            throw new \InvalidArgumentException(reset($error)[0]);
        }

        $staffsQuery = Staff::find()
            ->valid()
            ->company(false)
            ->division($searchModel->division_id)
            ->andFilterWhere(['{{%staffs}}.id' => $searchModel->staffs]);

        $staffs = $staffsQuery->all();

        $searchModel->staffs = $staffsQuery->select('{{%staffs}}.id')->asArray()->column();

        $timeRange = $user->company->getWorkingPeriod(
            $searchModel->start,
            $searchModel->end,
            $searchModel->staffs
        );

        $businessHours = $this->getBusinessHours(
            $searchModel->division_id,
            $searchModel->start,
            $searchModel->end,
            $staffs,
            $timeRange['min'],
            $timeRange['max'],
            $searchModel->viewName
        );

        $models = Yii::createObject('yii\rest\Serializer')->serialize($searchModel->search());

        return array_merge($models, $businessHours);
    }

    /**
     * Create order
     *
     * @return array|Order
     * @throws \Exception
     */
    public function actionAddEvent()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $form = new OrderCreateForm();
        $form->load(Yii::$app->request->post());

        if ( ! $form->validate()) {
            return ['errors' => $form->errors];
        }

        $orderServices = $this->getOrderServices($form->services);
        $orderProducts = $this->getOrderProducts($form->products);
        $orderPayments = $this->getOrderPayments($form->payments);
        $orderContacts = $this->getOrderContacts($form->contacts);

        try {
            $order = $this->orderService->create(
                new OrderData(
                    new DateTime($form->datetime),
                    $form->division_id,
                    $form->staff_id,
                    $form->note,
                    $form->hours_before,
                    $form->color,
                    $form->company_cash_id,
                    Yii::$app->user->id,
                    Yii::$app->user->identity->company_id,
                    $form->insurance_company_id,
                    $form->referrer_id
                ),
                $orderServices,
                $orderProducts,
                $orderPayments,
                $orderContacts,
                new CustomerData(
                    $form->company_customer_id,
                    $form->customer_name,
                    null,
                    null,
                    $form->customer_phone,
                    $form->customer_source_id
                )
            );
        } catch (\DomainException $e) {
            return ['errors' => [$e->getMessage()]];
        }

        return Yii::createObject('yii\rest\Serializer')->serialize($order);
    }

    /**
     * @param $id
     *
     * @return Order|array
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @throws \Exception
     */
    public function actionUpdateEvent($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $model = $this->findModel($id);
        if ( ! Yii::$app->user->can("orderUpdate", ['model' => $model])) {
            throw new NotFoundHttpException(
                'The requested page does not exist.'
            );
        }

        if ($model->status !== OrderConstants::STATUS_ENABLED) {
            throw new ForbiddenHttpException('Not Allowed to update');
        }

        $model->guardPassedDate();

        $form = new OrderUpdateForm($model);
        $form->load(Yii::$app->request->post());
        if ( ! $form->validate()) {
            return ['errors' => $form->errors];
        }

        $orderServices = $this->getOrderServices($form->services);
        $orderProducts = $this->getOrderProducts($form->products);
        $orderPayments = $this->getOrderPayments($form->payments);
        $orderContacts = $this->getOrderContacts($form->contacts);

        try {
            $order = $this->orderService->update(
                $model->id,
                false,
                new OrderData(
                    new DateTime($form->datetime),
                    $form->division_id,
                    $form->staff_id,
                    $form->note,
                    $form->hours_before,
                    $form->color,
                    $form->company_cash_id,
                    Yii::$app->user->id,
                    Yii::$app->user->identity->company_id,
                    $form->insurance_company_id,
                    $form->referrer_id
                ),
                $orderServices,
                $orderProducts,
                $orderPayments,
                $orderContacts,
                new CustomerData(
                    $form->company_customer_id,
                    $form->customer_name,
                    $form->customer_surname,
                    $form->customer_patronymic,
                    $form->customer_phone,
                    $form->customer_source_id
                )
            );

            $order->refresh();
        } catch (\DomainException $e) {
            return ['errors' => [$e->getMessage()]];
        }

        return Yii::createObject('yii\rest\Serializer')->serialize($order);
    }

    /**
     * @param $id
     *
     * @return Order|array
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @throws \Exception
     */
    public function actionCheckoutEvent($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $model = $this->findModel($id);
        if ( ! Yii::$app->user->can("orderUpdate", ['model' => $model])) {
            throw new NotFoundHttpException(
                'The requested page does not exist.'
            );
        }

        if ($model->status !== OrderConstants::STATUS_ENABLED) {
            throw new ForbiddenHttpException('Not Allowed to checkout');
        }

        $model->guardPassedDate();

        $form = new OrderUpdateForm($model);
        $form->load(Yii::$app->request->post());
        if ( ! $form->validate()) {
            return ['errors' => $form->errors];
        }

        $orderServices = $this->getOrderServices($form->services);
        $orderProducts = $this->getOrderProducts($form->products);
        $orderPayments = $this->getOrderPayments($form->payments);
        $orderContacts = $this->getOrderContacts($form->contacts);

        try {
            $order = $this->orderService->checkout(
                $model->id,
                false,
                new OrderData(
                    new DateTime($form->datetime),
                    $form->division_id,
                    $form->staff_id,
                    $form->note,
                    $form->hours_before,
                    $form->color,
                    $form->company_cash_id,
                    Yii::$app->user->id,
                    Yii::$app->user->identity->company_id,
                    $form->insurance_company_id,
                    $form->referrer_id
                ),
                $orderServices,
                $orderProducts,
                $orderPayments,
                $orderContacts,
                new CustomerData(
                    $form->company_customer_id,
                    $form->customer_name,
                    $form->customer_surname,
                    $form->customer_patronymic,
                    $form->customer_phone,
                    $form->customer_source_id
                ),
                $form->ignore_stock
            );

            $order->refresh();
        } catch (InsufficientStockLevel $e) {
            return ['errors' => ['ignore_stock' => $e->getMessage()]];
        } catch (\DomainException $e) {
            return ['errors' => [$e->getMessage()]];
        }

        return $order;
    }

    /**
     * @param $id
     *
     * @return array
     * @throws NotFoundHttpException
     * @throws \Exception
     */
    public function actionReturnEvent($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $model = $this->findModel($id);
        if ( ! Yii::$app->user->can("orderUpdate", ['model' => $model])) {
            throw new NotFoundHttpException(
                'The requested page does not exist.'
            );
        }

        $model->guardPassedDate();

        if ($model->status == OrderConstants::STATUS_FINISHED) {
            $this->orderService->reset($id);
        } else if ($model->status == OrderConstants::STATUS_CANCELED) {
            $this->orderService->enable($id);
        }

        return ["status" => "success"];
    }

    /**
     * Change order duration time
     *
     * @param integer $id
     *
     * @return array
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @throws \Exception
     */
    public function actionUpdateEventDuration($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $model = $this->findModel($id);

        if ( ! Yii::$app->user->can("orderUpdate", ['model' => $model])) {
            throw new NotFoundHttpException('The requested page does not exist.');
        }

        if ($model->status !== OrderConstants::STATUS_ENABLED) {
            throw new ForbiddenHttpException('Not Allowed to change duration');
        }

        $model->guardPassedDate();

        $finishDatetime = new DateTime(Yii::$app->request->getBodyParam('end'));
        $startDatetime  = new DateTime($model->datetime);
        if ($startDatetime >= $finishDatetime) {
            throw new BadRequestHttpException('Wrong end time');
        }

        $duration = abs(
                        $finishDatetime->getTimestamp()
                        - $startDatetime->getTimestamp()
                    ) / 60;

        $order = $this->orderService->updateDuration($model->id, $duration);

        return [
            'error'    => 0,
            'message'  => Yii::t('app', 'Successful saving'),
            'duration' => $order->duration,
            'services' => OrderService::map($order->orderServices)
        ];
    }

    /**
     * Change order after drop
     *
     * @param integer $id
     *
     * @return array
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @throws \Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function actionUpdateEventDrop($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $model = $this->findModel($id);

        if ( ! Yii::$app->user->can("orderUpdate", ['model' => $model])) {
            throw new NotFoundHttpException(
                'The requested page does not exist.'
            );
        }

        if ($model->status !== OrderConstants::STATUS_ENABLED) {
            throw new ForbiddenHttpException('Not Allowed to update');
        }

        $model->guardPassedDate();

        $form = new OrderMoveForm($model);
        $form->load(Yii::$app->request->getBodyParams());

        if ( ! $form->validate()) {
            throw new BadRequestHttpException(Json::encode($form->errors));
        }

        $this->orderService->move($model->id, $form->staff, $form->start);

        return [
            'error'   => 0,
            'message' => Yii::t('app', 'Successful saving'),
        ];
    }

    /**
     * @param $id
     *
     * @return array
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @throws \Exception
     */
    public function actionDeleteEvent($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $model = $this->findModel($id);

        if ( ! Yii::$app->user->can("orderDelete", ['model' => $model])) {
            throw new NotFoundHttpException(
                'The requested page does not exist.'
            );
        }

        $model->guardPassedDate();

        if ($model->status !== OrderConstants::STATUS_ENABLED
            && $model->status !== OrderConstants::STATUS_CANCELED) {
            throw new ForbiddenHttpException('Not Allowed to update');
        }

        $this->orderService->disable($model->id);

        return ['message' => 'OK'];
    }

    /**
     * @param $id
     *
     * @return array
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @throws \Exception
     */
    public function actionCancelEvent($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $model = $this->findModel($id);

        if ( ! Yii::$app->user->can("orderDelete", ['model' => $model])) {
            throw new NotFoundHttpException(
                'The requested page does not exist.'
            );
        }

        $model->guardPassedDate();

        if ($model->status !== OrderConstants::STATUS_ENABLED) {
            throw new ForbiddenHttpException('Not Allowed to update');
        }

        $this->orderService->cancel($model->id);

        return ['message' => 'OK'];
    }

    /**
     * Returns order history view
     *
     * @param integer $id
     *
     * @return array
     * @throws NotFoundHttpException
     */
    public function actionHistory($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $history = $this->findModel($id)
                        ->getOrderHistory()
                        ->orderBy("created_time ASC")
                        ->all();

        return array_map(
            function ($log) {
                return [
                    'created_at' => Yii::$app->formatter->asDatetime(
                        $log->created_time
                    ),
                    'action'     => OrderHistoryHelper::getActionLabel($log->action),
                    'datetime'   => Yii::$app->formatter->asDatetime(
                        $log->datetime
                    ),
                    'status'     => OrderConstants::getStatuses()[$log->status],
                    'user'       => $log->acting_user
                        ?: Yii::t(
                            'app', 'Undefine'
                        )
                ];
            }, $history
        );
    }

    /**
     * Returns client information
     *
     * @param string $term
     *
     * @return mixed
     */
    public function actionSearch($term)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $query = CompanyCustomer::find()->company();
        $query->joinWith(["customer", 'phones'], [true, true]);
        $query->orderBy('{{%customers}}.name ASC');
        $query->limit(20);

        $pattern = trim(str_replace('_', '', $term));
        $exploded_pattern = explode(' ', $pattern);
        $condition = [
            'or',
            ['like', '{{%customers}}.phone', $pattern],
            ['like', '{{%company_customer_phones}}.phone', $pattern],
        ];
        foreach ($exploded_pattern as $string_pattern) {
            array_push($condition, ['ilike', '{{%customers}}.name', $string_pattern]);
            array_push($condition, ['ilike', '{{%customers}}.lastname', $string_pattern]);
        }
        $query->andFilterWhere($condition);

        /**
         * @var CompanyCustomer[] $companyCustomers
         */
        $companyCustomers = $query->all();

        return $companyCustomers;
    }

    /**
     * @TODO Refactor. Use Search form
     * Returns staff list with schedule for given date
     *
     * @param string       $date
     * @param integer|null $division_id
     *
     * @return array
     */
    public function actionActiveStaff($date, $division_id = null)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $position_id = Yii::$app->request->getQueryParam('position_id', null);

        $datetime = DateTime::createFromFormat('d.m.Y', $date);
        if (!($datetime and $datetime->format('d.m.Y') == $date)) {
            throw new InvalidValueException('Incorrect date');
        }

        $enabledStaffIds = Staff::find()->valid()->division($division_id)->select('{{%staffs}}.id')->column();

        $staffHasOrder = Order::find()
            ->select('{{%orders}}.staff_id')
            ->division($division_id)
            ->visible()
            ->startFrom($datetime)
            ->to((clone $datetime))
            ->staff($enabledStaffIds)
            ->column();

        $staffWithSchedule = Staff::find()
            ->select('{{%staffs}}.id')
            ->division($division_id)
            ->valid()
            ->withSchedule($datetime)
            ->byIds($enabledStaffIds)
            ->column();

        $staff_ids = array_unique(array_merge(
            $staffHasOrder,
            $staffWithSchedule
        ));

        /* @var User $user */
        $user  = Yii::$app->user->identity;
        $staff = $user->staff;
        if ($staff !== null && $staff->see_own_orders) {
            $staff_ids = [$staff->id];
        }

        /* @var Staff[] $staffs_selected */
        $staffs_selected = Staff::find()
            ->company()
            ->joinWith(['companyPositions' => function (CompanyPositionQuery $query) use ($position_id) {
                return $query->position($position_id);
            }])
            ->andWhere(['{{%staffs}}.id' => $staff_ids])
            ->all();

        $resources = [];
        foreach ($staffs_selected as $staff) {
            foreach ($staff->divisions as $division) {
                if ($division->id !== intval($division_id) && $division_id !== null) {
                    continue;
                }

                $position      = $staff->companyPositions ? $staff->companyPositions[0] : null; // TODO check if could be sent multiple
                $position_name = $position !== null ? $position->name : null;
                $position_id   = $position !== null ? $position->id : null;
                $schedule      = $staff->getDateScheduleAt(
                    $division->id,
                    $datetime
                );
                $resources[]   = [
                    'id'             => $staff->id,
                    'eventClassName' => $staff->color,
                    'staff_id'       => $staff->id,
                    'division_id'    => $division->id,
                    'position_id'    => $position_id,
                    'position'       => $position_name,
                    'title'          => $staff->getFullName(),
                    'schedule'       => $schedule
                        ? $schedule->getAttributes(["start_at", "end_at"])
                        : null
                ];
            }
        }

        return $resources;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function actionAddPending()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $form = new PendingOrderForm();
        $form->load(Yii::$app->request->post());

        if ( ! $form->validate()) {
            throw new \DomainException('Error while adding pending order');
        }

        $model = $this->orderService->addPending(
            new CustomerData(
                $form->company_customer_id,
                $form->customer_name,
                null,
                null,
                $form->customer_phone,
                null
            ),
            $form->date,
            $form->note,
            $form->staff_id,
            $form->division_id
        );

        return array_merge($model->attributes, [
            'customer_name'  => $form->customer_name,
            'customer_phone' => $form->customer_phone
        ]);
    }

    /**
     * @param $id
     *
     * @return array
     * @throws NotFoundHttpException
     * @throws \Exception
     */
    public function actionUpdatePending($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $model = $this->findModel($id);

        if ( ! Yii::$app->user->can("orderUpdate", ['model' => $model])) {
            throw new NotFoundHttpException(
                'The requested page does not exist.'
            );
        }

        $form = new PendingOrderForm();
        $form->load(Yii::$app->request->post());

        if ( ! $form->validate()) {
            throw new NotFoundHttpException(Yii::t('app',
                'Invalid input data'));
        }

        $model = $this->orderService->editPending(
            $id,
            new CustomerData(
                $form->company_customer_id,
                $form->customer_name,
                null,
                null,
                $form->customer_phone,
                null
            ),
            $form->date,
            $form->note,
            $form->staff_id,
            $form->division_id
        );

        return array_merge($model->attributes, [
            'customer_name'  => $form->customer_name,
            'customer_phone' => $form->customer_phone
        ]);
    }

    /**
     * @param $id
     *
     * @return array
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @throws \Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function actionEnablePending($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $model = $this->findModel($id);

        if ( ! Yii::$app->user->can("orderUpdate", ['model' => $model])) {
            throw new NotFoundHttpException(
                'The requested page does not exist.'
            );
        }

        if ($model->status !== OrderConstants::STATUS_WAITING) {
            throw new ForbiddenHttpException('Not Allowed to enable');
        }

        $form = new OrderMoveForm($model);
        $form->load(Yii::$app->request->getBodyParams());
        $form->staff = $model->staff_id;

        if ( ! $form->validate()) {
            throw new BadRequestHttpException(Json::encode($form->errors));
        }

        $this->orderService->move($model->id, $model->staff_id, $form->start);
        $this->orderService->enable($model->id);

        return [
            'error'   => 0,
            'message' => Yii::t('app', 'Successful saving'),
        ];
    }

    /**
     * @param $id
     * @return array
     * @throws NotFoundHttpException
     */
    public function actionDeletePending($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $model = $this->findModel($id);

        if (!Yii::$app->user->can("orderUpdate", ['model' => $model])) {
            throw new NotFoundHttpException(
                'The requested page does not exist.'
            );
        }

        try {
            $this->orderService->deletePending($id);
        } catch (\DomainException $e) {
            return [
                'error'   => true,
                'message' => $e->getMessage()
            ];
        }

        return [
            'error'   => 0,
            'message' => Yii::t('app', 'Successful deleted')
        ];
    }

    public function actionWorkingPeriod()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $searchModel = new TimetableOrderSearch();
        $searchModel->load(Yii::$app->request->queryParams);

        if (!$searchModel->validate()) {
            $error = $searchModel->getErrors();
            throw new \InvalidArgumentException(reset($error)[0]);
        }

        /* @var User $user */
        $user = Yii::$app->user->identity;

        return $user->company->getWorkingPeriod(
            $searchModel->start,
            $searchModel->end,
            $searchModel->staffs
        );
    }

    /**
     * @return \core\models\File|array
     * @throws \Exception
     */
    public function actionUploadFile()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if ( ! Yii::$app->user->identity->company->canUploadFiles()) {
            return [
                'error' => Yii::t(
                    'yii',
                    'You are not allowed to perform this action.'
                )
            ];
        }

        $order_id = Yii::$app->request->post('id');
        if ($order_id && $form = UploadedFile::getInstanceByName('file')) {
            try {
                return $this->orderStorageService->upload(
                    Yii::$app->user->identity->company_id,
                    $order_id,
                    $form->name,
                    $form->tempName
                );
            } catch (\DomainException $e) {
                return [
                    'error'   => $e->getMessage(),
                    'message' => 'Произошла ошибка при загрузке файла'
                ];
            }
        }

        return ['error' => "Выберите файл"];
    }

    /**
     * @param $id
     *
     * @return array
     * @throws ForbiddenHttpException
     * @throws \Exception
     */
    public function actionDeleteFile($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            $this->orderStorageService->delete($id);

            return [
                'status'  => 200,
                'message' => Yii::t('app', 'Successful deleted')
            ];
        } catch (\DomainException $e) {
            return [
                'error'   => $e->getMessage(),
                'message' => 'Произошла ошибка при удалении файла'
            ];
        }
    }

    /**
     * Finds the Order model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param integer $id
     *
     * @return Order the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Order::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(
                'The requested page does not exist.'
            );
        }
    }

    /**
     * @param $division_id
     * @param $start
     * @param $end
     * @param $staffs
     * @param $min
     * @param $max
     * @param $table_view
     *
     * @return array
     */
    private function getBusinessHours(
        $division_id,
        $start,
        $end,
        $staffs,
        $min,
        $max,
        $table_view
    ) {
        $end_date  = (new DateTime($end))->modify("-1 day");
        $schedules = Staff::getScheduleAt(
            new DateTime($start),
            $end_date,
            $staffs
        );
        $items     = [];
        foreach ($schedules as $staff_id => $divisions) {
            foreach ($divisions as $d_id => $schedule) {
                if ($d_id !== intval($division_id)) {
                    continue;
                }
                foreach ($schedule as $date => $data) {
                    if ($data != null) {
                        $item = [
                            'rendering' => 'inverse-background',
                            'start'     => $data->start_at,
                            'end'       => $data->end_at,
                            'minTime'   => $data->start_at,
                            'maxTime'   => $data->end_at,
                            'className' => 'fc-nonbusiness'
                        ];
                        switch ($table_view) {
                            case TimetableHelper::VIEW_WEEK:
                                $item['id'] = 0;
                                if ($data->break_start && $data->break_end) {
                                    $items[] = array_merge($item, [
                                        'end' => $data->break_start
                                    ]);
                                    $item['start'] = $data->break_end;
                                }
                                break;
                            case TimetableHelper::VIEW_DAY:
                                $item['resourceId'] = $staff_id;
                                if ($data->break_start && $data->break_end) {
                                    $items[] = array_merge($item, [
                                        'rendering' => 'background',
                                        'start'     => $data->break_start,
                                        'end'       => $data->break_end
                                    ]);
                                }
                                break;
                            default:
                                $item['id'] = 0;
                                $item['start'] = $date;
                                $item['end'] = $date;
                                break;
                        }
                        $items[] = $item;
                    } else {
                        if ($table_view == TimetableHelper::VIEW_DAY) {
                            $item    = [
                                'rendering'  => 'background',
                                'resourceId' => $staff_id,
                                'start'      => $start . " 00:00",
                                'end'        => $end . " 00:00",
                                'minTime'    => $min,
                                'maxTime'    => $max,
                                'className'  => 'fc-nonbusiness'
                            ];
                            $items[] = $item;
                        }
                    }
                }
            }
        }
        if (empty($items)) {
            $item = [
                'rendering' => 'background',
                'start'     => $start,
                'end'       => $end,
                'minTime'   => $min,
                'maxTime'   => $max,
                'className' => 'fc-nonbusiness'
            ];
            if ($table_view != TimetableHelper::VIEW_MONTH) {
                $item['start'] .= ' 00:00';
                $item['end']   .= ' 00:00';
            }
            $items[] = $item;
        }

        return $items;
    }

    /**
     * @param array $services
     *
     * @return array
     */
    private function getOrderServices($services)
    {
        return array_map(
            function ($item) {
                return new OrderServiceData(
                    intval($item['division_service_id']),
                    intval($item['price']),
                    intval($item['duration']),
                    intval($item['discount']),
                    intval($item['quantity']),
                    intval($item['order_service_id']) ?: null
                );
            }, $services
        );
    }

    /**
     * @param array $contacts
     *
     * @return array
     */
    private function getOrderContacts($contacts)
    {
        if (empty($contacts)) {
            return [];
        }

        return array_map(
            function ($item) {
                return new OrderContactData(
                    intval($item['id']),
                    $item['phone'],
                    $item['name']
                );
            }, $contacts
        );
    }

    /**
     *
     * @param array $products
     *
     * @return ProductData[]
     */
    private function getOrderProducts($products)
    {
        if (empty($products)) {
            return [];
        }

        return array_map(
            function ($item) {
                return new ProductData(
                    intval($item['product_id']),
                    intval($item['quantity']),
                    abs(intval($item['price']))
                );
            }, $products
        );
    }

    /**
     * @param $orderPayments
     *
     * @return array
     */
    private function getOrderPayments($orderPayments)
    {
        return array_map(
            function ($item) {
                return new OrderPaymentData(
                    intval($item['payment_id']),
                    intval($item['amount'])
                );
            }, array_filter(
                $orderPayments,
                function ($item) {
                    return $item['amount'] > 0;
                }
            )
        );
    }

    /**
     * @param Company $company
     *
     * @return Response
     */
    private function gotoNewDesign(Company $company)
    {
        $host = $company->show_fullcalendar_view ? Yii::$app->params['calendar_host'] : Yii::$app->params['vue_host'];
        $access_token = Yii::$app->user->identity->getValidAccessToken();
        $new_design_link = "{$host}?access-token={$access_token}";

        return $this->redirect($new_design_link);
    }
}
