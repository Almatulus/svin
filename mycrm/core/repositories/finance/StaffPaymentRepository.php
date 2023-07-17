<?php


namespace core\repositories\finance;

use core\models\finance\CompanyCashflow;
use core\models\StaffPayment;
use core\repositories\BaseRepository;
use core\repositories\exceptions\NotFoundException;

class StaffPaymentRepository extends BaseRepository
{
    /**
     * @param integer $id
     * @return StaffPayment
     * @throws NotFoundException
     */
    public function find($id)
    {
        if (!$model = StaffPayment::findOne($id)) {
            throw new NotFoundException('Model not found.');
        }
        return $model;
    }

    /**
     * @param StaffPayment $staffPayment
     */
    public function deleteServices(StaffPayment $staffPayment)
    {
        $staffPayment->unlinkAll('services', true);
    }

    /**
     * @param StaffPayment $staffPayment
     */
    public function unlinkWithCashflow(StaffPayment $staffPayment)
    {
        $staffPayment->unlink('cashflow', $staffPayment->cashflow, true);
    }

    /**
     * @param StaffPayment $staffPayment
     * @param CompanyCashflow $cashflow
     */
    public function linkWithCashflow(StaffPayment $staffPayment, CompanyCashflow $cashflow)
    {
        $staffPayment->link('cashflow', $cashflow);
    }

}