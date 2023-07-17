<?php

namespace services;


use core\helpers\ScheduleTemplateHelper;
use core\models\division\Division;
use core\models\ScheduleTemplate;
use core\models\ScheduleTemplateInterval;
use core\models\Staff;
use core\models\StaffSchedule;
use core\models\user\User;
use core\services\staff\dto\ScheduleTemplateData;
use core\services\staff\dto\TemplateIntervalData;
use core\services\staff\ScheduleTemplateService;

class ScheduleTemplateServiceTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    /** @var ScheduleTemplateService */
    protected $service;

    /**
     * @param $type
     * @dataProvider generateProvider
     */
    public function testGenerate($type)
    {
        // register user component with service locator for BlamableBehaviour
        $user = $this->tester->getFactory()->create(User::class);
        \Yii::$app->set('user', $user);

        $staff = $this->tester->getFactory()->create(Staff::class);
        $division = $this->tester->getFactory()->create(Division::class);
        $staff->link('divisions', $division);

        $templateData = new ScheduleTemplateData(
            $staff->id,
            $division->id,
            ScheduleTemplateHelper::PERIOD_TWO_WEEKS,
            $type
        );
        $intervalsData = [
            1 => new TemplateIntervalData(1, "09:00", "22:00", "14:00", "15:00"),
            2 => new TemplateIntervalData(2, "05:00", "22:00", "13:00", "14:00")
        ];

        $start = new \DateTime();
        $start->setTime(0, 0, 0);
        $clonedStart = clone($start);

        $this->service->generate($templateData, $intervalsData, $clonedStart);

        $this->tester->canSeeRecord(ScheduleTemplate::class, [
            'staff_id'      => $staff->id,
            'division_id'   => $division->id,
            'interval_type' => ScheduleTemplateHelper::PERIOD_TWO_WEEKS,
            'type'          => $type
        ]);

        foreach ($intervalsData as $intervalsDatum) {
            $this->tester->canSeeRecord(ScheduleTemplateInterval::class, [
                'day'         => $intervalsDatum->day,
                'start'       => $intervalsDatum->start,
                'end'         => $intervalsDatum->end,
                'break_start' => $intervalsDatum->break_start,
                'break_end'   => $intervalsDatum->break_end
            ]);
        }

        $end = (clone $start)->modify("+ " . ScheduleTemplateHelper::periodValue(ScheduleTemplateHelper::PERIOD_TWO_WEEKS));

        $counter = 1;
        $additionalCondition = true;
        while ($start < $end) {

            $index = 0;
            switch ($templateData->type) {
                case ScheduleTemplateHelper::TYPE_DAYS_OF_WEEK:
                    $index = $start->format("N");
                    break;
                case ScheduleTemplateHelper::TYPE_THREE_TO_TWO:
                    $index = $counter;
                    $additionalCondition = $index <= 3;
                    $counter = $counter >= 5 ? 1 : ++$counter;
                    break;
                case ScheduleTemplateHelper::TYPE_ODD_EVEN:
                    $index = ($start->format('d') % 2) == 0 ? 2 : 1;
                    break;
            }

            if (isset($intervalsData[$index]) && $additionalCondition) {
                $intervalDatum = $intervalsData[$index];

                $this->tester->canSeeRecord(StaffSchedule::class, [
                    'staff_id'    => $staff->id,
                    'division_id' => $division->id,
                    'start_at'    => $start->format("Y-m-d") . " " . $intervalDatum->start,
                    'end_at'      => $start->format("Y-m-d") . " " . $intervalDatum->end,
                    'break_start' => $intervalDatum->break_start ? ($start->format("Y-m-d") . " " . $intervalDatum->break_start) : null,
                    'break_end'   => $intervalDatum->break_end ? ($start->format("Y-m-d") . " " . $intervalDatum->break_end) : null,
                ]);
            }

            $start->modify("+1 day");
        }
    }

    /**
     * @return array
     */
    public function generateProvider()
    {
        return [
            [ScheduleTemplateHelper::TYPE_DAYS_OF_WEEK],
            [ScheduleTemplateHelper::TYPE_THREE_TO_TWO],
            [ScheduleTemplateHelper::TYPE_ODD_EVEN],
        ];
    }

    protected function _before()
    {
        $this->service = \Yii::createObject(ScheduleTemplateService::class);
    }

    protected function _after()
    {
    }
}