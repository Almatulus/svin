<?php

namespace core\repositories\medCard;

use core\models\medCard\MedCardTabService;
use core\repositories\exceptions\NotFoundException;

class MedCardTabServiceRepository
{
    /**
     * @param $id
     * @return MedCardTabService
     * @throws NotFoundException
     */
    public function find($id)
    {
        if (!$model = MedCardTabService::findOne($id)) {
            throw new NotFoundException('Model not found.');
        }
        return $model;
    }

    /**
     * @param integer $med_card_tab_id
     *
     * @return MedCardTabService[]
     */
    public function findAllByMedCardTab($med_card_tab_id)
    {
        return MedCardTabService::find()
            ->where(['med_card_tab_id' => $med_card_tab_id])
            ->indexBy('division_service_id')
            ->all();
    }

    public function add(MedCardTabService $model)
    {
        if (!$model->getIsNewRecord()) {
            throw new \RuntimeException('Adding existing model.');
        }
        if (!$model->insert(false)) {
            throw new \RuntimeException('Saving error.');
        }
    }

    public function edit(MedCardTabService $model)
    {
        if ($model->getIsNewRecord()) {
            throw new \RuntimeException('Saving new model.');
        }
        if ($model->update(false) === false) {
            throw new \RuntimeException('Saving error.');
        }
    }

    public function delete(MedCardTabService $model)
    {
        if (!$model->delete()) {
            throw new \RuntimeException('Deleting error.');
        }
    }

    /**
     * @param int $med_card_tab_id
     */
    public function deleteAll(int $med_card_tab_id)
    {
        MedCardTabService::deleteAll(['med_card_tab_id' => $med_card_tab_id]);
    }
}