<?php

namespace core\repositories;

use core\models\StaffSchedule;
use core\repositories\exceptions\NotFoundException;
use DateTime;

class StaffScheduleRepository
{
    /**
     * @param $id
     * @return StaffSchedule
     * @throws NotFoundException
     */
    public function find($id)
    {
        if (!$model = StaffSchedule::findOne($id)) {
            throw new NotFoundException('Model not found.');
        }
        return $model;
    }

    /**
     * @param integer $staff_id
     * @param integer $division_id
     * @param string  $date
     *
     * @return StaffSchedule
     */
    public function findStaffSchedule($staff_id, $division_id, $date)
    {
        $date = DateTime::createFromFormat('Y-m-d', $date);
        /* @var StaffSchedule $model */
        $model = StaffSchedule::find()
                              ->where(':start_at <= start_at AND start_at <= :end_at',
                                  [

                                      ':start_at' => $date->format('Y-m-d 00:00:00'),
                                      ':end_at'   => $date->format('Y-m-d 23:59:59')
                                  ])
                              ->andWhere([
                                  'staff_id'    => $staff_id,
                                  'division_id' => $division_id,
                              ])
                              ->one();
        if ($model == null) {
            throw new NotFoundException('Model not found.');
        }

        return $model;
    }

    public function add(StaffSchedule $model)
    {
        if (!$model->getIsNewRecord()) {
            throw new \RuntimeException('Adding existing model.');
        }
        if (!$model->insert(false)) {
            throw new \RuntimeException('Saving error.');
        }
    }

    public function edit(StaffSchedule $model)
    {
        if ($model->getIsNewRecord()) {
            throw new \RuntimeException('Saving new model.');
        }
        if ($model->update(false) === false) {
            throw new \RuntimeException('Saving error.');
        }
    }

    public function delete(StaffSchedule $model)
    {
        if (!$model->delete()) {
            throw new \RuntimeException('Deleting error.');
        }
    }

    /**
     * @param integer $staff_id
     * @param $date
     */
    public function clear($staff_id, $date)
    {
        $date = DateTime::createFromFormat('Y-m-d', $date);
        StaffSchedule::deleteAll('staff_id = :staff_id AND :start_at <= start_at AND start_at <= :end_at', [
            ':staff_id' => $staff_id,
            ':start_at' => $date->format('Y-m-d 00:00:00'),
            ':end_at'   => $date->format('Y-m-d 23:59:59')
        ]);
    }

    /**
     * @param int $staff_id
     * @param int $division_id
     * @param DateTime $start
     * @param DateTime $end
     */
    public function clearByRange(int $staff_id, int $division_id, \DateTime $start, \DateTime $end)
    {
        StaffSchedule::deleteAll([
            "AND",
            ['staff_id' => $staff_id],
            ['division_id' => $division_id],
            [
                "OR",
                'start_at >= :start_at AND start_at <= :end_at',
                'end_at >= :start_at AND end_at <= :end_at'
            ]
        ], [
            ':start_at' => $start->format("Y-m-d"),
            ':end_at'   => $end->format("Y-m-d") . " 24:00:00"
        ]);
    }
}