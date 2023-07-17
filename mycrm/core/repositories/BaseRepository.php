<?php

namespace core\repositories;

use yii\db\ActiveRecord;

class BaseRepository
{
    /**
     * @param ActiveRecord $model
     */
    public function add(ActiveRecord $model)
    {
        if (!$model->getIsNewRecord()) {
            throw new \RuntimeException('Adding existing model.');
        }
        if (!$model->insert(false)) {
            throw new \RuntimeException('Saving error.');
        }
    }

    /**
     * @param ActiveRecord $model
     */
    public function edit(ActiveRecord $model)
    {
        if ($model->getIsNewRecord()) {
            throw new \RuntimeException('Saving new model.');
        }
        if ($model->update(false) === false) {
            throw new \RuntimeException('Saving error.');
        }
    }

    /**
     * @param ActiveRecord $model
     */
    public function delete(ActiveRecord $model)
    {
        if (!$model->delete()) {
            throw new \RuntimeException('Deleting error.');
        }
    }

    /**
     * @param ActiveRecord $model
     */
    public function softDelete(ActiveRecord $model)
    {
        if (!$model->updateAttributes(['deleted_time' => date('Y-m-d H:i:s')])) {
            throw new \RuntimeException('Deleting error.');
        }
    }

    /**
     * @param ActiveRecord $model
     */
    public function save(ActiveRecord $model)
    {
        if ( ! $model->save()) {
            $errors = $model->getErrors();
            throw new \RuntimeException('Saving error.: ' . reset($errors)[0]);
        }
    }
}
