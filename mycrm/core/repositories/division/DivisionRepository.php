<?php

namespace core\repositories\division;

use core\models\division\Division;
use core\models\division\DivisionPayment;
use core\repositories\exceptions\NotFoundException;

class DivisionRepository
{
    /**
     * @param $id
     * @return Division
     */
    public function find($id)
    {
        if (!$model = Division::findOne($id)) {
            throw new NotFoundException('Model not found.');
        }
        return $model;
    }

    public function add(Division $model)
    {
        if (!$model->getIsNewRecord()) {
            throw new \RuntimeException('Adding existing model.');
        }
        if (!$model->insert(false)) {
            throw new \RuntimeException('Saving error.');
        }
    }

    public function edit(Division $model)
    {
        if ($model->getIsNewRecord()) {
            throw new \RuntimeException('Saving new model.');
        }
        if ($model->update(false) === false) {
            throw new \RuntimeException('Saving error.');
        }
    }

    public function delete(Division $model)
    {
        if (!$model->delete()) {
            throw new \RuntimeException('Deleting error.');
        }
    }

    /**
     * @param int $division_id
     * @param int $payment_id
     * @return DivisionPayment
     */
    public function findPayment(int $division_id, int $payment_id)
    {
        if (!$model = DivisionPayment::findOne(['division_id' => $division_id, 'payment_id' => $payment_id])) {
            throw new NotFoundException('Заведение не использует данный способ оплаты.');
        }
        return $model;
    }
}