<?php

namespace core\repositories\medCard;

use core\models\medCard\MedCardCompanyComment;
use core\repositories\exceptions\NotFoundException;

class MedCardCompanyCommentRepository
{
    /**
     * @param $id
     *
     * @return MedCardCompanyComment
     * @throws NotFoundException
     */
    public function find($id)
    {
        if ( ! $model = MedCardCompanyComment::findOne($id)) {
            throw new NotFoundException('Model not found.');
        }

        return $model;
    }

    /**
     * @param integer $company_id
     * @param integer $category_id
     * @param string  $comment
     *
     * @return MedCardCompanyComment
     */
    public function findByCompanyCategoryComment(
        $company_id,
        $category_id,
        $comment
    ) {
        /* @var MedCardCompanyComment $model */
        $model = MedCardCompanyComment::findOne([
            'comment'     => $comment,
            'company_id'  => $company_id,
            'category_id' => $category_id,
        ]);

        if ( ! $model) {
            throw new NotFoundException('Model not found.');
        }

        return $model;
    }

    /**
     * @param MedCardCompanyComment $model
     */
    public function save(MedCardCompanyComment $model)
    {
        if ( ! $model->save()) {
            throw new \RuntimeException('Saving error.');
        }
    }

    /**
     * @param MedCardCompanyComment $model
     *
     * @throws \Exception
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function delete(MedCardCompanyComment $model)
    {
        if ( ! $model->delete()) {
            throw new \RuntimeException('Deleting error.');
        }
    }
}