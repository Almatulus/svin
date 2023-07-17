<?php

namespace core\repositories\warehouse;

use core\models\warehouse\Sale;
use core\models\warehouse\SaleProduct;
use core\repositories\BaseRepository;
use core\repositories\exceptions\NotFoundException;

class SaleRepository extends BaseRepository
{
    /**
     * @param $id
     * @return Sale
     */
    public function find($id)
    {
        if (!$model = Sale::findOne($id)) {
            throw new NotFoundException('Model not found.');
        }
        return $model;
    }

    /**
     * @param $product_id
     * @return SaleProduct
     */
    public function findProduct($product_id) {
        if (!$model = SaleProduct::findOne($product_id)) {
            throw new NotFoundException('Model not found.');
        }
        return $model;
    }

    /**
     * @param int $cashflow_id
     * @param int $sale_id
     */
    public function linkSaleWithCashflow(int $cashflow_id, int $sale_id)
    {
        $command = \Yii::$app->getDb()->createCommand();
        $command->insert("{{%company_cashflow_sales}}", [
            'cashflow_id' => $cashflow_id,
            'sale_id'     => $sale_id
        ])->execute();
    }


    /**
     * @param integer $sale_id
     */
    public function unlinkSaleWithCashflow(int $sale_id)
    {
        $command = \Yii::$app->getDb()->createCommand();
        $command->delete("{{%company_cashflow_sales}}", [
            'sale_id' => $sale_id
        ])->execute();
    }

    /**
     * @param $sale_id
     * @return int
     */
    public function deleteProducts($sale_id)
    {
        return SaleProduct::deleteAll(['sale_id' => $sale_id]);
    }

}