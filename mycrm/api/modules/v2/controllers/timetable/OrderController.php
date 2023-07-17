<?php

namespace api\modules\v2\controllers\timetable;

use api\modules\v2\controllers\BaseController;
use core\helpers\TimetableHelper;
use core\models\Staff;
use core\models\user\User;
use core\services\order\OrderModelService;
use DateTime;
use frontend\modules\order\search\TimetableOrderSearch;
use Yii;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;

class OrderController extends BaseController
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
                        'events',
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
     * @return array
     */
    public function actionEvents()
    {
        /* @var User $user */
        $user = Yii::$app->user->identity;
        $searchModel = new TimetableOrderSearch();
        $searchModel->company_id = $user->company_id;
        $searchModel->load(Yii::$app->request->queryParams);

        if (!$searchModel->validate()) {
            $errors = $searchModel->getErrors();
            throw new \InvalidArgumentException(reset($errors)[0]);
        }

        $staffs = Staff::find()
            ->valid()
            ->company()
            ->division($searchModel->division_id)
            ->andFilterWhere(['{{%staffs}}.id' => $searchModel->staffs])
            ->all();

        $searchModel->staffs = ArrayHelper::getColumn($staffs, 'id');

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

        return array_merge($searchModel->search()->getModels(), $businessHours);
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
}