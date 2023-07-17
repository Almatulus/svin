<?php

namespace core\services\staff;

use core\helpers\ScheduleTemplateHelper;
use core\models\ScheduleTemplate;
use core\models\ScheduleTemplateInterval;
use core\models\Staff;
use core\repositories\division\DivisionRepository;
use core\repositories\exceptions\NotFoundException;
use core\repositories\ScheduleTemplateRepository;
use core\repositories\StaffRepository;
use core\repositories\StaffScheduleRepository;
use core\services\staff\dto\ScheduleTemplateData;
use core\services\staff\dto\TemplateIntervalData;
use core\services\StaffScheduleService;
use core\services\TransactionManager;

class ScheduleTemplateService
{
    /** @var DivisionRepository */
    private $divisionRepository;
    /** @var StaffScheduleService */
    private $scheduleService;
    /** @var StaffRepository */
    private $staffRepository;
    /** @var  StaffScheduleRepository */
    private $scheduleRepository;
    /** @var  ScheduleTemplateRepository */
    private $scheduleTemplateRepository;
    /** @var TransactionManager */
    private $transactionManager;

    /**
     * ScheduleTemplateService constructor.
     * @param DivisionRepository $divisionRepository
     * @param StaffScheduleService $scheduleService
     * @param StaffRepository $staffRepository
     * @param StaffScheduleRepository $scheduleRepository
     * @param ScheduleTemplateRepository $scheduleTemplateRepository
     * @param TransactionManager $transactionManager
     */
    public function __construct(
        DivisionRepository $divisionRepository,
        StaffScheduleService $scheduleService,
        StaffRepository $staffRepository,
        StaffScheduleRepository $scheduleRepository,
        ScheduleTemplateRepository $scheduleTemplateRepository,
        TransactionManager $transactionManager
    ) {
        $this->divisionRepository = $divisionRepository;
        $this->scheduleService = $scheduleService;
        $this->staffRepository = $staffRepository;
        $this->scheduleRepository = $scheduleRepository;
        $this->scheduleTemplateRepository = $scheduleTemplateRepository;
        $this->transactionManager = $transactionManager;
    }

    /**
     * @param ScheduleTemplateData $templateData
     * @param TemplateIntervalData[] $intervalsData
     */
    public function generate(ScheduleTemplateData $templateData, $intervalsData, \DateTime $start)
    {
        $staff = $this->staffRepository->find($templateData->staff_id);
        $division = $this->divisionRepository->find($templateData->division_id);

        try {
            $model = $this->scheduleTemplateRepository->findByStaffAndDivision($staff->id, $division->id);
        } catch (NotFoundException $e) {
            $model = new ScheduleTemplate();
        }
        $model->staff_id = $templateData->staff_id;
        $model->division_id = $templateData->division_id;
        $model->type = $templateData->type;
        $model->interval_type = $templateData->interval_type;

        $intervals = $this->getIntervals($model, $intervalsData);

        $this->transactionManager->execute(function () use ($model, $intervals, $start) {
            $this->scheduleTemplateRepository->save($model);
            $this->scheduleTemplateRepository->clearIntervals($model->id);
            foreach ($intervals as $interval) {
                $interval->schedule_template_id = $model->id;
                $this->scheduleTemplateRepository->add($interval);
            }

            $this->generateSchedules($model, $start);
        });

        return $model;
    }

    /**
     * @param ScheduleTemplate $template
     * @param TemplateIntervalData[] $intervals
     * @return ScheduleTemplateInterval[]
     */
    protected function getIntervals(ScheduleTemplate $template, $intervals)
    {
        return array_map(function (TemplateIntervalData $intervalData) use ($template) {
            $interval = new ScheduleTemplateInterval();
            $interval->day = $intervalData->day;
            $interval->start = $intervalData->start;
            $interval->end = $intervalData->end;
            $interval->break_start = $intervalData->break_start;
            $interval->break_end = $intervalData->break_end;
            return $interval;
        }, $intervals);
    }

    /**
     * @param ScheduleTemplate $model
     * @param \DateTime $start_date
     */
    private function generateSchedules(ScheduleTemplate $model, \DateTime $start_date)
    {
        $intervals = $model->getIntervals()->indexBy('day')->all();
        $end_date = (new \DateTime($start_date->format("Y-m-d")))->modify("+ " . $model->getPeriod());

        $this->deleteSchedules($model->staff_id, $model->division_id, $start_date, $end_date);

        $index = 0;
        $additionalCondition = true;
        while ($start_date < $end_date) {
            switch ($model->type) {
                case ScheduleTemplateHelper::TYPE_DAYS_OF_WEEK:
                    $index = $start_date->format('N');
                    break;
                case ScheduleTemplateHelper::TYPE_THREE_TO_TWO:
                    $index = $index >= 5 ? 1 : ++$index;
                    $additionalCondition = $index <= 3;
                    break;
                case ScheduleTemplateHelper::TYPE_ODD_EVEN:
                    $index = ($start_date->format('d') % 2) == 0 ? 2 : 1;
                    break;
            }

            if (isset($intervals[$index]) && $additionalCondition) {
                $this->scheduleService->add(
                    $model->staff_id,
                    $model->division_id,
                    $start_date->format("Y-m-d"),
                    $intervals[$index]->break_start,
                    $intervals[$index]->break_end,
                    $intervals[$index]->start,
                    $intervals[$index]->end
                );

                Staff::invalidateDateSchedule(
                    $model->division_id,
                    $start_date
                );
            }

            $start_date->modify("+1 day");
        }
    }

    /**
     * @param int $staff_id
     * @param int $division_id
     * @param \DateTime $start_date
     * @param \DateTime $end_date
     */
    private function deleteSchedules(int $staff_id, int $division_id, \DateTime $start_date, \DateTime $end_date)
    {
        $localStartDate = clone $start_date;
        $this->scheduleRepository->clearByRange($staff_id, $division_id, $start_date, $end_date);

        while ($localStartDate < $end_date) {
            Staff::invalidateDateSchedule(
                $division_id,
                $localStartDate
            );
            $localStartDate->modify("+1 day");
        }
    }
}