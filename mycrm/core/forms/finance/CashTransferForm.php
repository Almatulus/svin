<?php

namespace core\forms\finance;

use core\models\finance\CompanyCash;
use core\models\finance\query\CashQuery;
use yii\base\Model;

class CashTransferForm extends Model
{
    public $cash_id;
    public $amount;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            ['cash_id', 'required'],
            ['cash_id', 'integer'],
            [
                'cash_id',
                'exist',
                'targetClass'     => CompanyCash::class,
                'targetAttribute' => 'id',
                'filter'          => function (CashQuery $query) {
                    return $query->company();
                }
            ],

            ['amount', 'required'],
            ['amount', 'integer', 'min' => 1]
        ];
    }

    public function attributeLabels()
    {
        return [
            'cash_id' => \Yii::t('app', 'Cash'),
            'amount'  => \Yii::t('app', 'Sum')
        ];
    }
}
