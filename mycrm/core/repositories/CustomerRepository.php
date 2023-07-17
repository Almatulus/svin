<?php

namespace core\repositories;

use core\models\customer\Customer;
use core\repositories\exceptions\NotFoundException;

class CustomerRepository
{
    /**
     * @param $id
     *
     * @return Customer
     */
    public function find($id)
    {
        if ( ! $model = Customer::findOne($id)) {
            throw new NotFoundException('Model not found.');
        }

        return $model;
    }

    public function save(Customer $model)
    {
        if ($model->save(false) === false) {
            throw new \RuntimeException('Saving error.');
        }
    }

    /**
     * @param Customer $model
     *
     * @throws \Exception
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function delete(Customer $model)
    {
        if ($model->delete() === false) {
            throw new \RuntimeException('Deleting error.');
        }
    }
}