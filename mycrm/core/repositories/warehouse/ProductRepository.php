<?php

namespace core\repositories\warehouse;

use core\models\warehouse\Product;
use core\models\warehouse\ProductType;
use core\repositories\exceptions\NotFoundException;

class ProductRepository
{
    /**
     * @param $id
     * @return Product
     */
    public function find($id)
    {
        if (!$model = Product::findOne($id)) {
            throw new NotFoundException('Model not found.');
        }
        return $model;
    }

    /**
     * @param $id
     * @return ProductType
     */
    public function findType($id)
    {
        if (!$model = ProductType::findOne($id)) {
            throw new NotFoundException('Model not found.');
        }
        return $model;
    }

    public function add(Product $model)
    {
        if (!$model->getIsNewRecord()) {
            throw new \RuntimeException('Adding existing model.');
        }
        if (!$model->insert(false)) {
            throw new \RuntimeException('Saving error.');
        }
    }

    public function edit(Product $model)
    {
        if ($model->getIsNewRecord()) {
            throw new \RuntimeException('Saving new model.');
        }
        if ($model->update(false) === false) {
            throw new \RuntimeException('Saving error.');
        }
    }

    public function delete(Product $model)
    {
        if (!$model->delete()) {
            throw new \RuntimeException('Deleting error.');
        }
    }
}