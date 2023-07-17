<?php

namespace core\repositories\finance;

use core\models\finance\CompanyCashflowProduct;
use core\repositories\BaseRepository;
use core\repositories\exceptions\NotFoundException;

class CompanyCashflowProductRepository extends BaseRepository
{
    /**
     * @param integer $id
     * @return CompanyCashflowProduct
     * @throws NotFoundException
     */
    public function find($id)
    {
        if (!$model = CompanyCashflowProduct::findOne($id)) {
            throw new NotFoundException('Model not found.');
        }
        return $model;
    }

    /**
     * @param integer $order_product_id
     * @return CompanyCashflowProduct
     */
    public function findByOrderProduct($order_product_id)
    {
        /* @var CompanyCashflowProduct $model */
        $model = CompanyCashflowProduct::findOne(['order_service_product_id' => $order_product_id]);
        if ($model == null) {
            throw new NotFoundException('Model not found.');
        }
        return $model;
    }

    /**
     * @param integer $order_id
     * @return CompanyCashflowProduct[]
     */
    public function findAllByOrder($order_id)
    {
        return CompanyCashflowProduct::find()
            ->joinWith('orderProduct')
            ->where(['{{%order_service_products}}.order_id' => $order_id])
            ->all();
    }
}