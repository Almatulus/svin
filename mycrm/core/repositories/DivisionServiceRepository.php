<?php

namespace core\repositories;

use core\models\division\DivisionService;
use core\repositories\exceptions\NotFoundException;

class DivisionServiceRepository
{
    /**
     * @param $id
     * @return DivisionService
     */
    public function find($id)
    {
        if (!$model = DivisionService::findOne($id)) {
            throw new NotFoundException('Model not found.');
        }
        return $model;
    }

    public function add(DivisionService $model)
    {
        if (!$model->getIsNewRecord()) {
            throw new \RuntimeException('Adding existing model.');
        }
        if (!$model->insert(false)) {
            throw new \RuntimeException('Saving error.');
        }
    }

    public function edit(DivisionService $model)
    {
        if ($model->getIsNewRecord()) {
            throw new \RuntimeException('Saving new model.');
        }
        if ($model->update(false) === false) {
            throw new \RuntimeException('Saving error.');
        }
    }

    public function delete(DivisionService $model)
    {
        if (!$model->delete()) {
            throw new \RuntimeException('Deleting error.');
        }
    }
}