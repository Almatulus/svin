<?php

namespace frontend\modules\admin\forms;

use core\models\company\Company;
use Yii;
use yii\base\Model;

class PaymentLogForm extends Model
{
    public $currency;
    public $description;
    public $message;
    public $value;

    public function rules()
    {
        return [
            [['value', 'currency'], 'required'],
            [['value', 'currency'], 'integer'],
            [['description', 'message'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'value' => Yii::t('app', 'Payment value'),
            'currency' => Yii::t('app', 'Payment Currency'),
            'code' => Yii::t('app', 'Code'),
            'created_time' => Yii::t('app', 'Created Time'),
            'confirmed_time' => Yii::t('app', 'Confirmed Time'),
            'description' => Yii::t('app', 'Description'),
            'message' => Yii::t('app', 'Message'),
        ];
    }

}