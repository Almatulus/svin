<?php

namespace core\repositories\order;

use core\models\order\OrderProduct;
use core\repositories\BaseRepository;
use core\repositories\exceptions\NotFoundException;

class OrderProductRepository extends BaseRepository
{
    /**
     * @param $id
     * @return OrderProduct
     */
    public function find($id)
    {
        if (!$model = OrderProduct::findOne($id)) {
            throw new NotFoundException('Model not found.');
        }
        return $model;
    }

    /**
     * @param $order_id
     * @param $product_id
     * @return OrderProduct
     */
    public function findByOrderAndProduct($order_id, $product_id)
    {
        /* @var OrderProduct $model */
        $model = OrderProduct::find()->where(['order_id' => $order_id, 'product_id' => $product_id])->one();
        if (!$model) {
            throw new NotFoundException('Model not found.');
        }
        return $model;
    }

    /**
     * @param integer $order_id
     * @return OrderProduct[]
     */
    public function findAllByOrder($order_id)
    {
        return OrderProduct::find()
            ->where([
                '{{%order_service_products}}.order_id'     => $order_id,
                '{{%order_service_products}}.deleted_time' => null,
            ])
            ->all();
    }
}