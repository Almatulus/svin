<?php

namespace core\forms\warehouse;

use core\models\Payment;
use core\models\Staff;
use core\models\customer\CompanyCustomer;
use core\models\finance\CompanyCash;
use Yii;
use yii\base\Model;

class SaleForm extends Model
{
    public $cash_id;
    public $company_customer_id;
    public $discount;
    public $division_id;
    public $paid;
    public $payment_id;
    public $sale_date;
    public $staff_id;

    public function init()
    {
        if (!$this->sale_date) $this->sale_date = date("Y-m-d");
    }

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['sale_date', 'division_id'], 'required'],
            [['cash_id', 'company_customer_id', 'discount', 'division_id', 'payment_id', 'staff_id'], 'integer'],
            [['paid'], 'number'],
            [['sale_date'], 'safe'],
            [['cash_id'], 'exist', 'skipOnError' => false, 'targetClass' => CompanyCash::className(), 'targetAttribute' => ['cash_id' => 'id']],
            [['company_customer_id'], 'exist', 'skipOnError' => false, 'targetClass' => CompanyCustomer::className(), 'targetAttribute' => ['company_customer_id' => 'id']],
            [['payment_id'], 'exist', 'skipOnError' => false, 'targetClass' => Payment::className(), 'targetAttribute' => ['payment_id' => 'id']],
            [['staff_id'], 'exist', 'skipOnError' => false, 'targetClass' => Staff::className(), 'targetAttribute' => ['staff_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'cash_id' => Yii::t('app', 'Cash ID'),
            'company_customer_id' => Yii::t('app', 'Customer'),
            'discount' => Yii::t('app', 'Discount'),
            'division_id' => Yii::t('app', 'Division ID'),
            'paid' => Yii::t('app', 'Paid'),
            'payment_id' => Yii::t('app', 'Payment'),
            'staff_id' => Yii::t('app', 'Staff ID'),
            'sale_date' => Yii::t('app', 'Sale date'),
        ];
    }

    /**
     * @return string
     */
    public function formName()
    {
        return 'Sale';
    }
}
