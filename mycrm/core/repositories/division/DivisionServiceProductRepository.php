<?php

namespace core\repositories\division;

use core\models\division\DivisionServiceProduct;
use core\repositories\BaseRepository;
use core\repositories\exceptions\NotFoundException;

class DivisionServiceProductRepository extends BaseRepository
{
    /**
     * @param $id
     * @return DivisionServiceProduct
     */
    public function find($id)
    {
        if (!$model = DivisionServiceProduct::findOne($id)) {
            throw new NotFoundException('Model not found.');
        }
        return $model;
    }

    /**
     * @param array $ids
     * @return int
     */
    public function batchDelete(array $ids)
    {
        return DivisionServiceProduct::deleteAll(['id' => $ids]);
    }

    /**
     * @param int $product_id
     * @return int
     */
    public function batchDeleteByProductId(int $product_id)
    {
        return DivisionServiceProduct::deleteAll(['product_id' => $product_id]);
    }
}