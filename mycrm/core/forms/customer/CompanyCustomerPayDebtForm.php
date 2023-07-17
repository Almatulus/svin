<?php

namespace core\forms\customer;

use common\components\Model;
use core\models\customer\CompanyCustomer;
use core\models\finance\CompanyCash;
use Yii;

/**
 * @property integer $id
 * @property integer $amount
 * @property integer $cash_id
 * @property integer $balance
 */
class CompanyCustomerPayDebtForm extends Model
{
    public $id;
    public $amount;
    public $balance;

    public function __construct(CompanyCustomer $model, array $config = [])
    {
        $this->id = $model->id;
        $this->balance = $model->balance;

        parent::__construct($config);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'amount', 'balance'], 'required'],

            ['id', 'integer'],
            ['amount', 'integer', 'min' => 1],
            ['balance', 'integer', 'max' => 0],
            ['amount', 'compare', 'compareValue' => 'balance', 'operator' => '>=', 'type' => 'number'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'Customer'),
            'amount' => Yii::t('app', 'Amount'),
            'balance' => Yii::t('app', 'Balance'),
        ];
    }


    public function formName()
    {
        return '';
    }
}
