<?php

namespace core\repositories\warehouse;

use core\models\warehouse\Category;
use core\models\warehouse\Manufacturer;
use core\repositories\exceptions\NotFoundException;

class CategoryRepository
{
    /**
     * @param $id
     * @return Category
     */
    public function find($id)
    {
        if (!$model = Category::findOne($id)) {
            throw new NotFoundException('Model not found.');
        }
        return $model;
    }

    /**
     * @param Category $model
     */
    public function add(Category $model)
    {
        if (!$model->getIsNewRecord()) {
            throw new \RuntimeException('Adding existing model.');
        }
        if (!$model->insert(false)) {
            throw new \RuntimeException('Saving error.');
        }
    }

    /**
     * @param Category $model
     */
    public function edit(Category $model)
    {
        if ($model->getIsNewRecord()) {
            throw new \RuntimeException('Saving new model.');
        }
        if ($model->update(false) === false) {
            throw new \RuntimeException('Saving error.');
        }
    }

}