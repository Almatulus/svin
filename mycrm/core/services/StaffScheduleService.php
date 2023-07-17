<?php

namespace core\services;

use core\models\StaffSchedule;
use core\repositories\division\DivisionRepository;
use core\repositories\StaffRepository;
use core\repositories\StaffScheduleRepository;

class StaffScheduleService
{
    private $staffScheduleRepository;
    private $staffRepository;
    private $divisionRepository;
    private $transactionManager;

    public function __construct(
        StaffScheduleRepository $staffScheduleRepository,
        StaffRepository $staffRepository,
        DivisionRepository $divisionRepository,
        TransactionManager $transactionManager
    )
    {
        $this->staffScheduleRepository = $staffScheduleRepository;
        $this->staffRepository = $staffRepository;
        $this->divisionRepository = $divisionRepository;
        $this->transactionManager = $transactionManager;
    }

    /**
     * @param integer $staff_id
     * @param integer $division_id
     * @param string $date
     * @param $break_start
     * @param $break_end
     * @param string|null $start
     * @param string|null $end
     * @return StaffSchedule
     */
    public function add($staff_id, $division_id, $date, $break_start, $break_end, $start = null, $end = null)
    {
        $staff = $this->staffRepository->find($staff_id);
        $division = $this->divisionRepository->find($division_id);

        $start_at = new \DateTime($date . ' ' . ($start ? $start : $division->working_start));
        $end_at = new \DateTime($date . ' ' . ($end ? $end : $division->working_finish));
        $staffSchedule = StaffSchedule::add(
            $staff,
            $division,
            $start_at->format('Y-m-d H:i:s'),
            $end_at->format('Y-m-d H:i:s'),
            $break_start ? ($date . ' ' . $break_start) : null,
            $break_start ? ($date . ' ' . $break_end) : null
        );
        $this->staffScheduleRepository->add($staffSchedule);
        return $staffSchedule;
    }

    /**
     * @param integer $staff_id
     * @param integer $division_id
     * @param string $date
     * @param string $start_time
     * @param string $end_time
     * @param $break_start
     * @param $break_end
     * @return StaffSchedule
     */
    public function edit($staff_id, $division_id, $date, $start_time, $end_time, $break_start, $break_end)
    {
        $staff = $this->staffRepository->find($staff_id);
        $division = $this->divisionRepository->find($division_id);
        $staffSchedule = $this->staffScheduleRepository->findStaffSchedule($staff->id, $division->id, $date);

        $start_at = new \DateTime($date . ' ' . $start_time);
        $end_at   = new \DateTime($date . ' ' . $end_time);
        $staffSchedule->edit(
            $start_at->format('Y-m-d H:i:s'),
            $end_at->format('Y-m-d H:i:s'),
            $break_start ? ($date . ' ' . $break_start) : null,
            $break_start ? ($date . ' ' . $break_end) : null
        );

        $this->transactionManager->execute(function () use ($staffSchedule) {
            $this->staffScheduleRepository->edit($staffSchedule);
        });

        return $staffSchedule;
    }

    /**
     * @param integer $staff_id
     * @param integer $division_id
     * @param string  $date
     */
    public function delete($staff_id, $division_id, $date)
    {
        $staff = $this->staffRepository->find($staff_id);
        $division = $this->divisionRepository->find($division_id);
        $staffSchedule = $this->staffScheduleRepository->findStaffSchedule($staff->id, $division->id, $date);

        $this->staffScheduleRepository->delete($staffSchedule);
    }
}
