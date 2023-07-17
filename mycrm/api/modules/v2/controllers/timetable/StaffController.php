<?php

namespace api\modules\v2\controllers\timetable;

use api\modules\v2\controllers\BaseController;
use api\modules\v2\search\timetable\StaffSearch;
use core\models\Staff;
use core\services\order\OrderModelService;
use DateTime;
use Yii;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;


class StaffController extends BaseController
{
    public $modelClass = 'core\models\order\Order';
    private $orderService;

    public function __construct(
        $id,
        $module,
        OrderModelService $orderService,
        $config = []
    ) {
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
                        'resources',
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
        unset($actions['index'], $actions['create'], $actions['update'], $actions['delete']);
        $actions['options']['collectionOptions'] = ["PUT", "PATCH", "POST", "GET", "HEAD", "OPTIONS", "DELETE"];

        return $actions;
    }

    /**
     * Returns staff list with schedule for given date
     *
     * @return array
     * @throws BadRequestHttpException
     */
    public function actionResources()
    {
        $searchModel = new StaffSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $datetime = new DateTime($searchModel->date);
        return array_map(function (Staff $staff) use ($datetime, $searchModel) {
            $schedule = $staff->getDateScheduleAt($searchModel->division_id, $datetime);
            return [
                'id'             => $staff->id,
                'eventClassName' => $staff->color,
                'name'           => $staff->getFullName(),
                'position'       => ArrayHelper::getColumn($staff->companyPositions, 'name'),
                'schedule'       => $schedule ? $schedule->getAttributes(["start_at", "end_at"]) : null
            ];
        }, $dataProvider->getModels());
    }
}
