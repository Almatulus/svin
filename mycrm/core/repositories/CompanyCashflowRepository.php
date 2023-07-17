<?php

namespace core\repositories;

use core\models\finance\CompanyCashflow;
use core\repositories\exceptions\NotFoundException;

class CompanyCashflowRepository extends BaseRepository
{
    /**
     * @param $id
     * @return CompanyCashflow
     * @throws NotFoundException
     */
    public function find($id)
    {
        if (!$model = CompanyCashflow::findOne($id)) {
            throw new NotFoundException('Model not found.');
        }
        return $model;
    }

    /**
     * @param $order_id
     * @return CompanyCashflow[]
     */
    public function findAllByOrder($order_id)
    {
        return CompanyCashflow::find()->order($order_id)->all();
    }

    /**
     * @param $staff_id
     * @param $salary_cost_item_id
     * @param $salary
     * @param $created_time
     *
     * @return CompanyCashflow
     */
    public function findStaffSalaryPaymentForDate(
        $staff_id,
        $salary_cost_item_id,
        $salary,
        $created_time
    ) {
        return CompanyCashflow::find()
            ->where([
                'cost_item_id' => $salary_cost_item_id,
                'staff_id' => $staff_id,
                'value' => $salary,
                'receiver_mode' => CompanyCashflow::RECEIVER_STAFF
            ])
            ->andWhere([
                '>',
                'date',
                (new \DateTime($created_time))->modify('-1 minute')
                    ->format('Y-m-d H:i:s')
            ])
            ->andWhere([
                '<',
                'date',
                (new \DateTime($created_time))->modify('+1 minute')
                    ->format('Y-m-d H:i:s')
            ])
            ->one();
    }
}
