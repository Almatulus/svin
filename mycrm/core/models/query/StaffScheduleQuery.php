<?php

namespace core\models\query;
use yii\db\ActiveQuery;

class StaffScheduleQuery extends ActiveQuery
{

    public function between($startDate, $endDate)
    {
        return $this->andWhere(':startDate <= start_at AND end_at <= :endDate',
            [
                ':startDate' => $startDate,
                ':endDate'  => $endDate,
            ]);
    }

    /**
     * Filter by division_id
     * @param integer $division_id
     * @return StaffScheduleQuery
     */
    public function division($division_id)
    {
        return $this->andWhere(['{{%staff_schedules}}.division_id' => $division_id]);
    }
}