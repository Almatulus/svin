<?php

namespace core\repositories\medCard;

use core\models\medCard\MedCardComment;
use core\repositories\exceptions\NotFoundException;

class MedCardCommentRepository
{
    /**
     * @param string  $comment
     * @param integer $category_id
     *
     * @return MedCardComment
     */
    public function findByCommentAndCategory($comment, $category_id)
    {
        /* @var MedCardComment $model */
        $model = MedCardComment::findOne([
            'comment' => $comment,
            'category_id' => $category_id
        ]);

        if (!$model) {
            throw new NotFoundException('Model not found.');
        }
        return $model;
    }

    /**
     * @param $id
     * @return MedCardComment
     * @throws NotFoundException
     */
    public function find($id)
    {
        if (!$model = MedCardComment::findOne($id)) {
            throw new NotFoundException('Model not found.');
        }
        return $model;
    }

    /**
     * @param MedCardComment $model
     */
    public function add(MedCardComment $model)
    {
        if (!$model->getIsNewRecord()) {
            throw new \RuntimeException('Adding existing model.');
        }
        if (!$model->insert(false)) {
            throw new \RuntimeException('Saving error.');
        }
    }

    /**
     * @param MedCardComment $model
     */
    public function edit(MedCardComment $model)
    {
        if ($model->getIsNewRecord()) {
            throw new \RuntimeException('Saving new model.');
        }
        if ($model->update(false) === false) {
            throw new \RuntimeException('Saving error.');
        }
    }

    /**
     * @param MedCardComment $model
     */
    public function delete(MedCardComment $model)
    {
        if (!$model->delete()) {
            throw new \RuntimeException('Deleting error.');
        }
    }
}