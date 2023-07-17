<?php

namespace core\forms;


use common\components\Model;
use core\helpers\customer\CustomerHelper;
use Yii;

class PhoneForm extends Model
{
    public $phone;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            ['phone', 'required'],
            ['phone', 'string'],
            ['phone', 'match', 'pattern' => CustomerHelper::PHONE_VALIDATE_PATTERN],
        ];
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return ['phone' => Yii::t('app', 'Phone')];
    }
}
