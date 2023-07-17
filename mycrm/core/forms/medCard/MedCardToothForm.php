<?php

namespace core\forms\medCard;

use core\helpers\medCard\MedCardToothHelper;
use yii\base\Model;

/**
 * @property integer $number
 * @property integer $diagnosis_id
 * @property integer $mobility
 */
class MedCardToothForm extends Model
{
    public $number;
    public $diagnosis_id;
    public $mobility;

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['number'], 'required'],
            [['number', 'diagnosis_id', 'mobility'], 'integer'],
            ['number', 'in', 'range' => MedCardToothHelper::allTeeth()]
        ];
    }

    public function formName()
    {
        return 'MedCardTooth';
    }
}
