<?php

namespace core\repositories\customer;

use core\models\customer\CustomerSource;
use core\repositories\exceptions\NotFoundException;

class CustomerSourceRepository
{
    /**
     * @param $id
     *
     * @return CustomerSource
     * @throws NotFoundException
     */
    public function find($id)
    {
        if ( ! $model = CustomerSource::findOne($id)) {
            throw new NotFoundException('Model not found.');
        }

        return $model;
    }

    /**
     * @param CustomerSource $model
     *
     * @throws \Exception|\Throwable
     */
    public function add(CustomerSource $model)
    {
        if ( ! $model->getIsNewRecord()) {
            throw new \RuntimeException('Adding existing model.');
        }
        if ( ! $model->insert(false)) {
            throw new \RuntimeException('Saving error.');
        }
    }

    /**
     * @param CustomerSource $model
     *
     * @throws \Exception
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function edit(CustomerSource $model)
    {
        if ($model->getIsNewRecord()) {
            throw new \RuntimeException('Saving new model.');
        }
        if ($model->update(false) === false) {
            throw new \RuntimeException('Saving error.');
        }
    }

    /**
     * @param CustomerSource $model
     *
     * @throws \Exception
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function delete(CustomerSource $model)
    {
        if ( ! $model->delete()) {
            throw new \RuntimeException('Deleting error.');
        }
    }
}