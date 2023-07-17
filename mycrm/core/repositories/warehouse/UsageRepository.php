<?php

namespace core\repositories\warehouse;

use core\models\warehouse\Usage;
use core\models\warehouse\UsageProduct;
use core\repositories\BaseRepository;
use core\repositories\exceptions\NotFoundException;

class UsageRepository extends BaseRepository
{
    /**
     * @param $id
     * @return Usage
     */
    public function find($id)
    {
        if (!$model = Usage::findOne($id)) {
            throw new NotFoundException('Model not found.');
        }
        return $model;
    }

    /**
     * @param $order_id
     * @return Usage
     */
    public function findByOrder($order_id)
    {
        if (!$model = Usage::find()->active()->order($order_id)->one()) {
            throw new NotFoundException('Model not found.');
        }
        return $model;
    }

    /**
     * @param int $usage_product_id
     * @return mixed
     */
    public function findProduct(int $usage_product_id)
    {
        if (!$model = UsageProduct::findOne($usage_product_id)) {
            throw new NotFoundException('Model not found.');
        }
        return $model;
    }

    /**
     * @param $ids
     * @return int
     */
    public function deleteProducts($ids)
    {
        return UsageProduct::deleteAll(['id' => $ids]);
    }

}