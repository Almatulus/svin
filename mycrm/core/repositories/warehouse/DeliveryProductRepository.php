<?php

namespace core\repositories\warehouse;

use core\models\warehouse\Delivery;
use core\models\warehouse\DeliveryProduct;
use core\repositories\BaseRepository;
use core\repositories\exceptions\NotFoundException;

class DeliveryProductRepository
{
    /**
     * @param $id
     * @return DeliveryProduct
     */
    public function find($id)
    {
        if (!$model = DeliveryProduct::findOne($id)) {
            throw new NotFoundException('Model not found.');
        }
        return $model;
    }

    /**
     * @param DeliveryProduct $model
     * @throws \Throwable
     */
    public function add(DeliveryProduct $model)
    {
        if (!$model->getIsNewRecord()) {
            throw new \RuntimeException('Adding existing model.');
        }
        if (!$model->insert(false)) {
            throw new \RuntimeException('Saving error.');
        }
    }

    /**
     * @param DeliveryProduct $model
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function edit(DeliveryProduct $model)
    {
        if ($model->getIsNewRecord()) {
            throw new \RuntimeException('Saving new model.');
        }
        if ($model->update(false) === false) {
            throw new \RuntimeException('Saving error.');
        }
    }

    /**
     * @param DeliveryProduct $model
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function delete(DeliveryProduct $model)
    {
        if (!$model->delete()) {
            throw new \RuntimeException('Deleting error.');
        }
    }

}