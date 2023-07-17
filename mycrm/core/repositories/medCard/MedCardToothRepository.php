<?php

namespace core\repositories\medCard;

use core\models\medCard\MedCardTooth;
use core\repositories\BaseRepository;

class MedCardToothRepository extends BaseRepository
{
    /**
     * @param int $med_card_id
     * @param int $tooth_num
     * @return bool
     */
    public function isTeethInMedCard(int $med_card_id, int $tooth_num)
    {
        return MedCardTooth::find()->joinWith('medCardTab')->where([
            'teeth_num' => $tooth_num,
            'med_card_id' => $med_card_id
        ])->exists();
    }

    /**
     * @param $med_card_tab_id
     * @return int
     */
    public function deleteAll(int $med_card_tab_id)
    {
        return MedCardTooth::deleteAll(['med_card_tab_id' => $med_card_tab_id]);
    }
}