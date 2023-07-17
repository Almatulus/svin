<?php

namespace core\repositories\division;

use core\models\division\DivisionPayment;
use core\repositories\BaseRepository;
use core\repositories\exceptions\NotFoundException;

class DivisionPaymentRepository extends BaseRepository
{
    /**
     * @param $id
     *
     * @return DivisionPayment
     */
    public function find($id)
    {
        if ( ! $model = DivisionPayment::findOne($id)) {
            throw new NotFoundException('Model not found.');
        }

        return $model;
    }

    /**
     * @param $division_id
     * @param $payment_id
     *
     * @return DivisionPayment
     */
    public function findByPayment($division_id, $payment_id)
    {
        /* @var $model DivisionPayment */
        $model = DivisionPayment::find()->where([
            'division_id' => $division_id,
            'payment_id'  => $payment_id
        ])->one();
        if ($model === null) {
            throw new NotFoundException('Model not found.');
        }

        return $model;
    }

    /**
     * @param integer $division_id
     *
     * @return DivisionPayment[]
     */
    public function findAllByDivision($division_id)
    {
        return DivisionPayment::find()
            ->where([
                'division_id' => $division_id,
                'status'      => DivisionPayment::STATUS_ENABLED
            ])
            ->orderBy('payment_id')
            ->all();
    }

    /**
     * @param $division_id
     *
     * @return int
     */
    public function deletePayments($division_id)
    {
        return DivisionPayment::updateAll(
            ['status' => DivisionPayment::STATUS_DISABLED],
            ['division_id' => $division_id]
        );
    }
}