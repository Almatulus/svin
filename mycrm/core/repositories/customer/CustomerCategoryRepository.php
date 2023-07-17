<?php

namespace core\repositories\customer;

use core\models\customer\CustomerCategory;
use core\repositories\exceptions\NotFoundException;

class CustomerCategoryRepository
{
    /**
     * @param $id
     *
     * @return CustomerCategory
     * @throws NotFoundException
     */
    public function find($id)
    {
        if ( ! $model = CustomerCategory::findOne($id)) {
            throw new NotFoundException('Model not found.');
        }

        return $model;
    }

    /**
     * @param CustomerCategory $model
     *
     * @throws \Exception|\Throwable
     */
    public function add(CustomerCategory $model)
    {
        if ( ! $model->getIsNewRecord()) {
            throw new \RuntimeException('Adding existing model.');
        }
        if ( ! $model->insert(false)) {
            throw new \RuntimeException('Saving error.');
        }
    }

    /**
     * @param CustomerCategory $model
     *
     * @throws \Exception
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function edit(CustomerCategory $model)
    {
        if ($model->getIsNewRecord()) {
            throw new \RuntimeException('Saving new model.');
        }
        if ($model->update(false) === false) {
            throw new \RuntimeException('Saving error.');
        }
    }

    /**
     * @param CustomerCategory $model
     *
     * @throws \Exception
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function delete(CustomerCategory $model)
    {
        if ( ! $model->delete()) {
            throw new \RuntimeException('Deleting error.');
        }
    }
}