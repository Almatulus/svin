<?php

namespace core\repositories\medCard;

use core\models\medCard\MedCard;
use core\models\medCard\MedCardTab;
use core\repositories\BaseRepository;
use core\repositories\exceptions\NotFoundException;

class MedCardRepository extends BaseRepository
{
    /**
     * @param $id
     * @return MedCard
     * @throws NotFoundException
     */
    public function find($id)
    {
        if (!$model = MedCard::findOne($id)) {
            throw new NotFoundException('Model not found.');
        }
        return $model;
    }

    /**
     * @param $order_id
     * @return MedCard
     * @throws NotFoundException
     */
    public function findByOrder($order_id)
    {
        if (!$model = MedCard::find()->where(['order_id' => $order_id])->one()) {
            throw new NotFoundException('Model not found.');
        }
        return $model;
    }

    /**
     * @param $id
     * @return MedCardTab
     * @throws NotFoundException
     */
    public function findTab($id)
    {
        if (!$model = MedCardTab::findOne($id)) {
            throw new NotFoundException('Model not found.');
        }
        return $model;
    }
}