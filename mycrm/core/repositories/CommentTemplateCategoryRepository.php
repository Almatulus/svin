<?php

namespace core\repositories;

use core\models\medCard\MedCardCommentCategory;
use core\repositories\exceptions\NotFoundException;

class CommentTemplateCategoryRepository
{
    /**
     * @param $id
     *
     * @return MedCardCommentCategory
     */
    public function find($id)
    {
        if (!$model = MedCardCommentCategory::findOne($id)) {
            throw new NotFoundException('Model not found.');
        }
        return $model;
    }

    /**
     * @param MedCardCommentCategory $model
     */
    public function add(MedCardCommentCategory $model)
    {
        if (!$model->getIsNewRecord()) {
            throw new \RuntimeException('Adding existing model.');
        }
        if (!$model->insert(false)) {
            throw new \RuntimeException('Saving error.');
        }
    }

    /**
     * @param MedCardCommentCategory $model
     */
    public function edit(MedCardCommentCategory $model)
    {
        if ($model->getIsNewRecord()) {
            throw new \RuntimeException('Saving new model.');
        }
        if ($model->update(false) === false) {
            throw new \RuntimeException('Saving error.');
        }
    }

    /**
     * @param MedCardCommentCategory $model
     */
    public function delete(MedCardCommentCategory $model)
    {
        if (!$model->delete()) {
            throw new \RuntimeException('Deleting error.');
        }
    }
}