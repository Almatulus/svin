<?php

namespace core\repositories\warehouse;

use core\models\warehouse\Delivery;
use core\models\warehouse\DeliveryProduct;
use core\repositories\BaseRepository;
use core\repositories\exceptions\NotFoundException;

class DeliveryRepository extends BaseRepository
{
    /**
     * @param $id
     * @return Delivery
     */
    public function find($id)
    {
        if (!$model = Delivery::findOne($id)) {
            throw new NotFoundException('Model not found.');
        }
        return $model;
    }

    /**
     * @param $product_id
     * @return DeliveryProduct
     */
    public function findProduct($product_id) {
        if (!$model = DeliveryProduct::findOne($product_id)) {
            throw new NotFoundException('Model not found.');
        }
        return $model;
    }

    /**
     * @param $delivery_id
     * @return int
     */
    public function deleteProducts($delivery_id)
    {
        return DeliveryProduct::deleteAll(['delivery_id' => $delivery_id]);
    }

}