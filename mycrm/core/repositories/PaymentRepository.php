<?php

namespace core\repositories;

use core\models\Payment;
use core\repositories\exceptions\NotFoundException;

class PaymentRepository
{
    /**
     * @param $id
     * @return Payment
     */
    public function find($id)
    {
        if (!$model = Payment::findOne($id)) {
            throw new NotFoundException('Model not found.');
        }
        return $model;
    }

    /**
     * Returns first payment model sorted by id
     * @return Payment
     */
    public function findFirst()
    {
        /* @var Payment $model */
        if (!$model = Payment::find()->orderBy('id')->one()) {
            throw new NotFoundException('Model not found.');
        }
        return $model;
    }

    public function add(Payment $model)
    {
        if (!$model->getIsNewRecord()) {
            throw new \RuntimeException('Adding existing model.');
        }
        if (!$model->insert(false)) {
            throw new \RuntimeException('Saving error.');
        }
    }

    public function edit(Payment $model)
    {
        if ($model->getIsNewRecord()) {
            throw new \RuntimeException('Saving new model.');
        }
        if ($model->update(false) === false) {
            throw new \RuntimeException('Saving error.');
        }
    }

    public function delete(Payment $model)
    {
        if (!$model->delete()) {
            throw new \RuntimeException('Deleting error.');
        }
    }
}