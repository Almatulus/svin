<?php

namespace api\modules\v2\controllers\order;

use api\modules\v2\controllers\BaseController;
use api\modules\v2\search\order\OrderSearch;
use core\forms\order\OrderCreateForm;
use core\forms\order\OrderMoveForm;
use core\forms\order\OrderOverlapForm;
use core\forms\order\OrderUpdateForm;
use core\helpers\order\OrderConstants;
use core\helpers\TimetableHelper;
use core\models\order\Order;
use core\models\Staff;
use core\services\dto\CustomerData;
use core\services\order\dto\OrderContactData;
use core\services\order\dto\OrderData;
use core\services\order\dto\OrderPaymentData;
use core\services\order\dto\OrderServiceData;
use core\services\order\dto\ProductData;
use core\services\order\OrderModelService;
use DateTime;
use Yii;
use yii\filters\AccessControl;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

class DefaultController extends BaseController
{
    public $modelClass = 'core\models\order\Order';
    private $orderService;

    public function __construct(
        $id,
        $module,
        OrderModelService $orderService,
        $config = []
    )
    {
        $this->orderService = $orderService;
        parent::__construct($id, $module, $config = []);
    }

    /**
     * @return array
     */
    public function behaviors(): array
    {
        $behaviors = parent::behaviors();

        $behaviors['access'] = [
            'class' => AccessControl::className(),
            'rules' => [
                [
                    'actions' => [
                        'index',
                        'events',
                        'create',
                        'options',
                        'update',
                        'checkout',
                        'return',
                        'enable',
                        'update-duration',
                        'drop',
                        'delete',
                        'cancel',
                        'history',
                        'view',
                        'overlapping',
                        'export',
                    ],
                    'allow'   => true,
                    'roles'   => ['@'],
                ],
            ],
        ];

        return $behaviors;
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        $actions = parent::actions();
        unset($actions['create'], $actions['update'], $actions['delete']);
        $actions['index']['prepareDataProvider'] = [
            $this,
            'prepareDataProvider'
        ];

        $actions['options']['collectionOptions'] = ["PUT", "PATCH", "POST", "GET", "HEAD", "OPTIONS", "DELETE"];

        return $actions;
    }

    /**
     * @inheritdoc
     */
    public function prepareDataProvider()
    {
        $searchModel = new OrderSearch();

        return $searchModel->search(Yii::$app->request->queryParams);
    }

    /**
     * @return OrderCreateForm|Order
     * @throws \Exception
     */
    public function actionCreate()
    {
        $form = new OrderCreateForm();
        $form->attributes = Yii::$app->request->bodyParams;

        if ($form->validate()) {
            $orderServices = $this->getOrderServices($form->services);
            $orderProduct = $this->getOrderProducts($form->products);
            $orderPayments = $this->getOrderPayments($form->payments);
            $orderContacts = $this->getOrderContacts($form->contacts);

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
                $orderProduct,
                $orderPayments,
                $orderContacts,
                new CustomerData(
                    $form->company_customer_id,
                    $form->customer_name,
                    $form->customer_surname,
                    $form->customer_patronymic,
                    $form->customer_phone,
                    $form->customer_source_id,
                    $form->customer_medical_record_id,
                    $form->customer_birth_date,
                    $form->customer_gender,
                    $form->insurance_company_id,
                    $form->categories
                ),
                $form->customer_source_name,
                $form->referrer_name
            );

            // refresh to fetch order number which is generated in database
            $order->refresh();

            return $order;
        }

        return $form;
    }

    /**
     * @param $id
     *
     * @return OrderUpdateForm|Order
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @throws \Exception
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        if (!Yii::$app->user->can("orderUpdate", ['model' => $model])) {
            throw new NotFoundHttpException(
                'The requested page does not exist.'
            );
        }

        if ($model->status !== OrderConstants::STATUS_ENABLED) {
            throw new ForbiddenHttpException('Not Allowed to update');
        }

        $model->guardPassedDate();

        $form = new OrderUpdateForm($model);
        $form->attributes = Yii::$app->request->bodyParams;
        if ($form->validate()) {
            $orderServices = $this->getOrderServices($form->services);
            $orderProduct = $this->getOrderProducts($form->products);
            $orderPayments = $this->getOrderPayments($form->payments);
            $orderContacts = $this->getOrderContacts($form->contacts);

            $order = $this->orderService->update(
                $model->id,
                $form->services_disabled,
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
                $orderProduct,
                $orderPayments,
                $orderContacts,
                new CustomerData(
                    $form->company_customer_id,
                    $form->customer_name,
                    $form->customer_surname,
                    $form->customer_patronymic,
                    $form->customer_phone,
                    $form->customer_source_id,
                    null,
                    $form->customer_birth_date,
                    $form->customer_gender,
                    $form->insurance_company_id,
                    $form->categories
                ),
                $form->customer_source_name,
                $form->referrer_name
            );

            $order->refresh();

            return $order;
        }

        return $form;
    }

    /**
     * @param $id
     *
     * @return OrderUpdateForm|Order
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @throws \Exception
     */
    public function actionCheckout($id)
    {
        $model = $this->findModel($id);
        if (!Yii::$app->user->can("orderUpdate", ['model' => $model])) {
            throw new NotFoundHttpException(
                'The requested page does not exist.'
            );
        }

        if ($model->status !== OrderConstants::STATUS_ENABLED) {
            throw new ForbiddenHttpException('Not Allowed to checkout');
        }

        $model->guardPassedDate();

        $form = new OrderUpdateForm($model);
        $form->attributes = Yii::$app->request->bodyParams;
        if ($form->validate()) {
            $orderServices = $this->getOrderServices($form->services);
            $orderProduct = $this->getOrderProducts($form->products);
            $orderPayments = $this->getOrderPayments($form->payments);
            $orderContacts = $this->getOrderContacts($form->contacts);

            $order = $this->orderService->checkout(
                $model->id,
                $form->services_disabled,
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
                $orderProduct,
                $orderPayments,
                $orderContacts,
                new CustomerData(
                    $form->company_customer_id,
                    $form->customer_name,
                    null,
                    null,
                    $form->customer_phone,
                    $form->customer_source_id,
                    null,
                    $form->customer_birth_date,
                    $form->customer_gender,
                    $form->insurance_company_id,
                    $form->categories
                )
            );

            $order->refresh();

            return $order;
        }

        return $form;
    }

    /**
     * @param $id
     *
     * @return Order
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @throws \Exception
     */
    public function actionReturn($id)
    {
        $model = $this->findModel($id);
        if (!Yii::$app->user->can("orderUpdate", ['model' => $model])) {
            throw new NotFoundHttpException(
                'The requested page does not exist.'
            );
        }

        if ($model->status !== OrderConstants::STATUS_FINISHED) {
            throw new ForbiddenHttpException('Not Allowed to checkout');
        }

        $model->guardPassedDate();

        return $this->orderService->reset($id);
    }

    /**
     * Enable canceled order
     *
     * @param $id
     *
     * @return Order
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @throws \Exception
     */
    public function actionEnable($id)
    {
        $model = $this->findModel($id);
        if (!Yii::$app->user->can("orderUpdate", ['model' => $model])) {
            throw new NotFoundHttpException(
                'The requested page does not exist.'
            );
        }

        if ($model->status !== OrderConstants::STATUS_CANCELED) {
            throw new ForbiddenHttpException('Not Allowed to checkout');
        }

        $model->guardPassedDate();

        return $this->orderService->enable($id);
    }

    /**
     * Change order duration time
     *
     * @param integer $id
     *
     * @return Order
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @throws \Exception
     */
    public function actionUpdateDuration($id)
    {
        $model = $this->findModel($id);

        if (!Yii::$app->user->can("orderUpdate", ['model' => $model])) {
            throw new NotFoundHttpException('The requested page does not exist.');
        }

        if ($model->status !== OrderConstants::STATUS_ENABLED) {
            throw new ForbiddenHttpException('Not Allowed to change duration');
        }

        $model->guardPassedDate();

        $finishDatetime = new DateTime(Yii::$app->request->getBodyParam('end'));
        $startDatetime = new DateTime($model->datetime);
        if ($startDatetime >= $finishDatetime) {
            throw new BadRequestHttpException('Wrong end time');
        }

        $duration = abs(
                $finishDatetime->getTimestamp()
                - $startDatetime->getTimestamp()
            ) / 60;

        $order = $this->orderService->updateDuration($model->id, $duration);

        return $order;
    }

    /**
     * Change order after drop
     *
     * @param integer $id
     *
     * @return OrderMoveForm|Order
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @throws \Exception
     */
    public function actionDrop($id)
    {
        $model = $this->findModel($id);

        if (!Yii::$app->user->can("orderUpdate", ['model' => $model])) {
            throw new NotFoundHttpException(
                'The requested page does not exist.'
            );
        }

        if ($model->status !== OrderConstants::STATUS_ENABLED) {
            throw new ForbiddenHttpException('Not Allowed to update');
        }

        $model->guardPassedDate();

        $form = new OrderMoveForm($model);
        $form->attributes = Yii::$app->request->bodyParams;

        if ($form->validate()) {
            return $this->orderService->move($model->id, $form->staff, $form->start);
        }

        return $form;
    }

    /**
     * @param $id
     *
     * @return array
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @throws \Exception
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        if (!Yii::$app->user->can("orderDelete", ['model' => $model])) {
            throw new NotFoundHttpException(
                'The requested page does not exist.'
            );
        }

        if ($model->status !== OrderConstants::STATUS_ENABLED
            && $model->status !== OrderConstants::STATUS_CANCELED) {
            throw new ForbiddenHttpException('Not Allowed to update');
        }

        $model->guardPassedDate();

        return $this->orderService->disable($model->id);
    }

    /**
     * @param $id
     *
     * @return Order
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @throws \Exception
     */
    public function actionCancel($id)
    {
        $model = $this->findModel($id);

        if (!Yii::$app->user->can("orderDelete", ['model' => $model])) {
            throw new NotFoundHttpException(
                'The requested page does not exist.'
            );
        }

        if ($model->status !== OrderConstants::STATUS_ENABLED) {
            throw new ForbiddenHttpException('Not Allowed to update');
        }

        $model->guardPassedDate();

        return $this->orderService->cancel($model->id);
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
        return $this->findModel($id)
            ->getOrderHistory()
            ->orderBy("created_time ASC")
            ->all();
    }

    /**
     * Returns orders in excel format
     */
    public function actionExport()
    {
        $searchModel = new OrderSearch();

        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $this->orderService->export($dataProvider);
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
    )
    {
        $end_date = (new DateTime($end))->modify("-1 day");
        $schedules = Staff::getScheduleAt(
            new DateTime($start),
            $end_date,
            $staffs
        );
        $items = [];
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
                            $item = [
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
                $item['end'] .= ' 00:00';
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
                    intval($item['division_service_id'] ?? $item['id']),
                    intval($item['price']),
                    intval($item['duration']),
                    intval($item['discount']),
                    intval($item['quantity']),
                    isset($item['order_service_id']) ? intval($item['order_service_id']) : null
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
     * @return array|OrderOverlapForm
     */
    public function actionOverlapping()
    {
        $form = new OrderOverlapForm();

        if ($form->load(Yii::$app->request->bodyParams, "") && $form->validate()) {
            return ['overlapping' => $this->orderService->isOrderOverlapping($form)];
        }

        return $form;
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
}
