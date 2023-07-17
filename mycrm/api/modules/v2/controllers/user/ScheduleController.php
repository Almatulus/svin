<?php

namespace api\modules\v2\controllers\user;

use api\modules\v2\controllers\BaseController;
use api\modules\v2\search\user\OrderSearch;
use api\modules\v2\search\user\StaffSearch;
use core\forms\StaffScheduleCreateForm;
use core\forms\StaffScheduleDeleteForm;
use core\forms\StaffScheduleUpdateForm;
use core\models\Staff;
use core\models\StaffSchedule;
use core\services\StaffScheduleService;
use DateTime;
use Yii;
use yii\filters\AccessControl;
use yii\web\BadRequestHttpException;

class ScheduleController extends BaseController
{
    public $modelClass = "core\models\StaffSchedule";

    /** @var StaffScheduleService */
    private $staffScheduleService;

    /**
     * ScheduleController constructor.
     * @param string $id
     * @param \yii\base\Module $module
     * @param StaffScheduleService $staffScheduleService
     * @param array $config
     */
    public function __construct(
        $id,
        $module,
        StaffScheduleService $staffScheduleService,
        $config = []
    ) {
        $this->staffScheduleService = $staffScheduleService;
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
            'only'  => ['index', 'create', 'update', 'delete', 'options', 'week'],
            'rules' => [
                [
                    'allow' => true,
                    'roles' => ['@'],
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
        unset($actions['index'], $actions['view'], $actions['create'], $actions['update'], $actions['delete']);

        return $actions;
    }

    /**
     * @return array
     * @throws BadRequestHttpException
     */
    public function actionIndex()
    {
        $params = Yii::$app->request->queryParams;
        $searchModel  = new StaffSearch();
        $dataProvider = $searchModel->search($params);

        /* @var Staff[] $staffs */
        $staffs = $dataProvider->getModels();
        $result = [];
        foreach ($staffs as $staff) {
            $orderSearch  = new OrderSearch(['staff_id' => $staff->id]);
            $dataProvider = $orderSearch->search($params);

            /* @var StaffSchedule $dateSchedule */
            $start_at = $end_at = $break_start = $break_end = null;
            $dateSchedule = $staff->getDateScheduleAt(
                $orderSearch->division_id,
                new DateTime($orderSearch->date)
            );
            if ($dateSchedule) {
                $start_at
                    = (new DateTime($dateSchedule->start_at))->format('H:i');
                $end_at
                    = (new DateTime($dateSchedule->end_at))->format('H:i');
                $break_start
                    = isset($dateSchedule->break_start) ? (new DateTime($dateSchedule->break_start))->format('H:i'): null;
                $break_end
                    = isset($dateSchedule->break_end) ? (new DateTime($dateSchedule->break_end))->format('H:i'): null;
            }

            $result[] = [
                'id'          => $staff->id,
                'start'       => $start_at,
                'end'         => $end_at,
                'break_start' => $break_start,
                'break_end'   => $break_end,
                'orders'      => $this->serializeData($dataProvider),
                'staff'       => $this->serializeData($staff),
            ];
        }

        return $result;
    }

    /**
     * @return StaffScheduleCreateForm|StaffSchedule
     */
    public function actionCreate()
    {
        $form = new StaffScheduleCreateForm();
        $form->load(Yii::$app->request->getBodyParams());

        if ($form->validate()) {
            $schedule = $this->staffScheduleService->add(
                $form->staff_id,
                $form->division_id,
                $form->date,
                $form->break_start,
                $form->break_end
            );

            Staff::invalidateDateSchedule(
                $form->division_id,
                new DateTime($form->date)
            );

            return $schedule;
        }

        return $form;
    }

    /**
     * @return StaffScheduleUpdateForm|StaffSchedule
     */
    public function actionUpdate()
    {
        $form = new StaffScheduleUpdateForm();
        $form->load(Yii::$app->request->getBodyParams());

        if ($form->validate()) {
            $schedule = $this->staffScheduleService->edit(
                $form->staff_id,
                $form->division_id,
                $form->date,
                $form->start,
                $form->end,
                $form->break_start,
                $form->break_end
            );

            Staff::invalidateDateSchedule(
                $form->division_id,
                new DateTime($form->date)
            );

            return $schedule;
        }

        return $form;
    }

    /**
     * @return StaffScheduleDeleteForm
     */
    public function actionDelete()
    {
        $form = new StaffScheduleDeleteForm();
        $form->load(Yii::$app->request->getQueryParams());

        if ($form->validate()) {
            $this->staffScheduleService->delete(
                $form->staff_id,
                $form->division_id,
                $form->date
            );

            Staff::invalidateDateSchedule(
                $form->division_id,
                new DateTime($form->date)
            );

            Yii::$app->response->setStatusCode(204);

            return null;
        }

        return $form;
    }

    /**
     * @return array
     * @throws BadRequestHttpException
     */
    public function actionWeek()
    {
        $params = Yii::$app->request->queryParams;
        $searchModel = new StaffSearch();
        $dataProvider = $searchModel->search($params);

        /* @var Staff[] $staffs */
        $staffs = $dataProvider->getModels();
        $items = [];

        foreach ($staffs as $staff) {
            $orderSearch = new OrderSearch(['staff_id' => $staff->id]);

            $result = [
                'id'    => $staff->id,
                'staff' => $this->serializeData($staff),
            ];

            $start = new \DateTime($params['date'] ?? null);
            $end = isset($params['end']) ? new \DateTime($params['end']) : clone($start);

            while ($start <= $end) {
                $dataProvider = $orderSearch->search(array_merge($params, ['date' => $start->format("Y-m-d")]));

                /* @var StaffSchedule $dateSchedule */
                $start_at = $end_at = $break_start = $break_end = null;
                $dateSchedule = $staff->getDateScheduleAt(
                    $params['division_id'],
                    new DateTime($orderSearch->date)
                );

                if ($dateSchedule) {
                    $start_at
                        = (new DateTime($dateSchedule->start_at))->format('H:i');
                    $end_at
                        = (new DateTime($dateSchedule->end_at))->format('H:i');
                    $break_start
                        = isset($dateSchedule->break_start) ? (new DateTime($dateSchedule->break_start))->format('H:i'): null;
                    $break_end
                        = isset($dateSchedule->break_end) ? (new DateTime($dateSchedule->break_end))->format('H:i'): null;
                }

                $result['schedules'][$start->format("Y-m-d")] = [
                    'start'       => $start_at,
                    'end'         => $end_at,
                    'break_start' => $break_start,
                    'break_end'   => $break_end,
                    'orders'      => $this->serializeData($dataProvider),
                ];

                $start->modify("+1 day");
            }

            $items[] = $result;
        }

        return $items;
    }

}
