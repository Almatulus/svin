<?php

namespace core\forms\document;

use core\helpers\medCard\MedCardToothHelper;
use core\models\medCard\MedCardToothDiagnosis;
use yii\base\Model;

class ToothForm extends Model
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
            [['number', 'diagnosis_id'], 'required'],
            [['number', 'diagnosis_id', 'mobility'], 'integer'],
            ['number', 'in', 'range' => array_values(MedCardToothHelper::allTeeth())],
            [
                'diagnosis_id',
                'in',
                'range' => MedCardToothDiagnosis::find()->where([
                    'company_id' => \Yii::$app->user->identity->company_id
                ])->select('id')->column()
            ],
        ];
    }

}