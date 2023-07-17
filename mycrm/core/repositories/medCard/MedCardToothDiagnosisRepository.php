<?php

namespace core\repositories\medCard;

use core\models\medCard\MedCardToothDiagnosis;
use core\repositories\exceptions\NotFoundException;

class MedCardToothDiagnosisRepository
{
    /**
     * @param $id
     *
     * @return MedCardToothDiagnosis
     * @throws NotFoundException
     */
    public function find($id)
    {
        if ( ! $model = MedCardToothDiagnosis::findOne($id)) {
            throw new NotFoundException('Model not found.');
        }

        return $model;
    }

    public function add(MedCardToothDiagnosis $model)
    {
        if ( ! $model->getIsNewRecord()) {
            throw new \RuntimeException('Adding existing model.');
        }
        if ( ! $model->insert(false)) {
            throw new \RuntimeException('Saving error.');
        }
    }

    public function edit(MedCardToothDiagnosis $model)
    {
        if ($model->getIsNewRecord()) {
            throw new \RuntimeException('Saving new model.');
        }
        if ($model->update(false) === false) {
            throw new \RuntimeException('Saving error.');
        }
    }

    public function delete(MedCardToothDiagnosis $model)
    {
        if ( ! $model->delete()) {
            throw new \RuntimeException('Deleting error.');
        }
    }
}