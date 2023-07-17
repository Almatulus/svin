<?php

namespace core\forms\insurance;

use core\models\InsuranceCompany;
use yii\base\Model;

class InsuranceForm extends Model
{
    public $companies;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            ['companies', 'required'],
            ['companies', 'each', 'rule' => ['integer']],
            [
                'companies',
                'each',
                'rule' => [
                    'exist',
                    'skipOnError'     => false,
                    'targetClass'     => InsuranceCompany::className(),
                    'targetAttribute' => 'id'
                ]
            ],
        ];
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'companies' => \Yii::t('app', 'Companies'),
        ];
    }
}