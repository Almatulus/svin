<?php

namespace core\repositories\warehouse;

use core\models\warehouse\Product;
use core\models\warehouse\Stocktake;
use core\models\warehouse\StocktakeProduct;
use core\repositories\BaseRepository;
use core\repositories\exceptions\NotFoundException;

class StocktakeRepository extends BaseRepository
{
    /**
     * @param $id
     * @return Stocktake
     */
    public function find($id)
    {
        if (!$model = Stocktake::findOne($id)) {
            throw new NotFoundException('Model not found.');
        }
        return $model;
    }

    public function current()
    {
        $currentStocktake = Stocktake::find()->company()->permitted()->andWhere([
            "<>",
            'status',
            Stocktake::STATUS_COMPLETED
        ])->one();

        return $currentStocktake;
    }

    /**
     * @param $ids
     * @return int
     */
    public function deleteProducts($ids)
    {
        return StocktakeProduct::deleteAll(['id' => $ids]);
    }

    public function divisionHasProducts($division_id, $type_of_products, $category_id)
    {
        return Product::find()
            ->company()
            ->division($division_id)
            ->filterByType($type_of_products)
            ->filterByCategory($category_id)
            ->exists();
    }

}