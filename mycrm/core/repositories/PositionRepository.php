<?php

namespace core\repositories;

use core\models\Position;
use core\repositories\exceptions\NotFoundException;

class PositionRepository
{
    /**
     * @param $id
     *
     * @return Position
     * @throws NotFoundException
     */
    public function find($id)
    {
        if (!$model = Position::findOne($id)) {
            throw new NotFoundException('Model not found.');
        }
        return $model;
    }

    public function add(Position $model)
    {
        if (!$model->getIsNewRecord()) {
            throw new \RuntimeException('Adding existing model.');
        }
        if (!$model->insert(false)) {
            throw new \RuntimeException('Saving error.');
        }
    }

    public function edit(Position $model)
    {
        if ($model->getIsNewRecord()) {
            throw new \RuntimeException('Saving new model.');
        }
        if ($model->update(false) === false) {
            throw new \RuntimeException('Saving error.');
        }
    }

    public function delete(Position $model)
    {
        if (!$model->softDelete()) {
            throw new \RuntimeException('Deleting error.');
        }
    }
}