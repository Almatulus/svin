<?php

namespace core\forms\staff;

use core\models\division\DivisionService;
use yii\base\Model;

class ServicesForm extends Model
{
    public $services;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            ['services', 'required'],
            ['services', 'each', 'rule' => ['integer']],
            [
                'services',
                'each',
                'rule' => ['exist', 'targetClass' => DivisionService::class, 'targetAttribute' => 'id']
            ],
        ];
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'services' => \Yii::t('app', 'Services')
        ];
    }

    /**
     * @return string
     */
    public function formName()
    {
        return "";
    }
}