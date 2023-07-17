<?php

namespace api\modules\v2\controllers\order;

use api\modules\v2\controllers\BaseController;
use api\modules\v2\OptionsTrait;
use core\forms\timetable\ActiveStaffForm;
use core\models\company\query\CompanyPositionQuery;
use core\models\order\Order;
use core\models\Staff;
use core\models\user\User;
use DateTime;

class TimetableController extends BaseController
{
    use OptionsTrait;

    public $modelClass = false;

    public function __construct(
        $id,
        $module,
        $config = []
    )
    {
        parent::__construct($id, $module, $config = []);
    }

    public function beforeAction($action)
    {
        $this->getOptionsHeaders();

        return parent::beforeAction($action);
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        $actions = parent::actions();
        unset($actions['create'], $actions['update'], $actions['delete'], $actions['index']);
        return $actions;
    }

    /**
     *
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
        $user = \Yii::$app->user->identity;
        $staff = $user->staff;
        if ($staff && $staff->see_own_orders) {
            $staffQuery->andWhere(['{{%staffs}}.id' => $staff->id]);
            $selectedStaffQuery->andWhere(['{{%staffs}}.id' => $staff->id]);
        }

        $staffs_selected = $selectedStaffQuery->select(['{{%staffs}}.id'])->column();
        $staffs = $staffQuery->all();

        return [
            'staff'          => $staffs,
            'selected_staff' => $staffs_selected,
            'duration'       => $user->company->getWorkingPeriod()
        ];
    }

    /**
     * @return array|ActiveStaffForm
     */
    public function actionResources()
    {
        $form = new ActiveStaffForm();
        $form->load(\Yii::$app->request->post());

        if ($form->validate()) {
            $staffHasOrder = Order::find()
                ->select('{{%orders}}.staff_id')
                ->division($form->division_id)
                ->visible()
                ->startFrom($form->datetime)
                ->to((clone $form->datetime)->modify("+1 day"))
                ->column();

            $staffWithSchedule = Staff::find()
                ->select('{{%staffs}}.id')
                ->division($form->division_id)
                ->valid()
                ->withSchedule($form->datetime)
                ->column();

            $staff_ids = array_unique(array_merge(
                $staffHasOrder,
                $staffWithSchedule
            ));

            /* @var User $user */
            $user = \Yii::$app->user->identity;
            $staff = $user->staff;
            if ($staff !== null && $staff->see_own_orders) {
                $staff_ids = array_unique(array_merge($staff_ids, $staff->id));
            }

            /* @var Staff[] $staffs_selected */
            $staffs_selected = Staff::find()
                ->joinWith(['companyPositions' => function(CompanyPositionQuery $query) use ($form) {
                    return $query->position($form->position_id);
                }])
                ->andWhere(['{{%staffs}}.id' => $staff_ids])
                ->all();

            $resources = [];
            foreach ($staffs_selected as $staff) {
                foreach ($staff->divisions as $division) {
                    $position = $staff->companyPositions ? $staff->companyPositions[0] : null; // TODO check this
                    $position_name = $position !== null ? $position->name : null;
                    $position_id = $position !== null ? $position->id : null;
                    $schedule = $staff->getDateScheduleAt(
                        $division->id,
                        $form->datetime
                    );
                    $resources[] = [
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

        return $form;
    }
}