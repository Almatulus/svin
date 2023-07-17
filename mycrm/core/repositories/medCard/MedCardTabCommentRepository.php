<?php

namespace core\repositories\medCard;

use core\models\medCard\MedCardTabComment;
use core\repositories\exceptions\NotFoundException;

class MedCardTabCommentRepository
{
    /**
     * @param $id
     * @return MedCardTabComment
     */
    public function find($id)
    {
        if (!$model = MedCardTabComment::findOne($id)) {
            throw new NotFoundException('Model not found.');
        }
        return $model;
    }

    /**
     * @deprecated
     * @param int $order_id
     * @param int $comment_template_category_id
     * @return MedCardTabComment
     */
    public function findByOrderAndCategory(int $order_id, int $comment_template_category_id)
    {
        /* @var MedCardTabComment $model */
        $model = MedCardTabComment::find()->where([
            'order_id' => $order_id,
            'category_id' => $comment_template_category_id
        ])->one();
        if (!$model) {
            throw new NotFoundException('Model not found.');
        }
        return $model;
    }

    /**
     * @param int $med_card_tab_id
     * @param int $comment_template_category_id
     * @return MedCardTabComment
     */
    public function findByMedCardTabAndCategory(int $med_card_tab_id, int $comment_template_category_id)
    {
        /* @var MedCardTabComment $model */
        $model = MedCardTabComment::find()->where([
            'med_card_tab_id' => $med_card_tab_id,
            'category_id' => $comment_template_category_id
        ])->one();
        if (!$model) {
            throw new NotFoundException('Model not found.');
        }
        return $model;
    }

    public function add(MedCardTabComment $model)
    {
        if (!$model->getIsNewRecord()) {
            throw new \RuntimeException('Adding existing model.');
        }
        if (!$model->insert(false)) {
            throw new \RuntimeException('Saving error.');
        }
    }

    public function edit(MedCardTabComment $model)
    {
        if ($model->getIsNewRecord()) {
            throw new \RuntimeException('Saving new model.');
        }
        if ($model->update(false) === false) {
            throw new \RuntimeException('Saving error.');
        }
    }

    public function delete(MedCardTabComment $model)
    {
        if (!$model->delete()) {
            throw new \RuntimeException('Deleting error.');
        }
    }

    /**
     * @param $med_card_tab_id
     */
    public function deleteAll($med_card_tab_id) {
        MedCardTabComment::deleteAll(['med_card_tab_id' => $med_card_tab_id]);
    }
}