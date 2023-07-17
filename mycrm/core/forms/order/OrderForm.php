<?php

namespace core\forms\order;

use core\helpers\customer\CustomerHelper;
use core\models\Staff;
use core\models\division\Division;
use core\models\finance\CompanyCash;
use Yii;
use yii\base\Model;

class OrderForm extends Model {

    public $id;
    public $color;
    public $company_cash_id;
    public $company_customer_id;
    public $customer_name;
    public $customer_lastname;
    public $customer_phone;
    public $datetime;
    public $discount;
    public $division_id;
    public $division_service_id;
    public $hours_before;
    public $ignore_stock;
    public $ignoreNameWarning;
    public $notify_status;
    public $note;
    public $price;
    public $staff_id;
    public $insurance_company_id;
    public $referrer_id;
    public $status;
    public $order_total_price;
    public $customer_source_id;

    public $products;
    public $services;
    public $payments;
    public $child_tooth;
    public $tooth;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['datetime', 'division_id', 'customer_name', 'services', 'services', 'staff_id', 'order_total_price'], 'required'],
            [['id', 'company_cash_id', 'company_customer_id', 'division_id', 'staff_id', 'status', 'order_total_price', 'insurance_company_id', 'referrer_id'], 'integer'],
            [['ignore_stock', 'ignoreNameWarning'], 'boolean'],

            [['hours_before'], 'integer', 'min' => 0],
            [['discount'], 'default', 'value' => 0],

            [['color', 'customer_phone', 'customer_name', 'customer_lastname', 'note'], 'string'],
            [['child_tooth', 'products', 'services', 'payments', 'tooth'], 'safe'],

            ['customer_phone', 'match', 'pattern' => CustomerHelper::PHONE_VALIDATE_PATTERN],

            [['discount'], 'integer', 'min' => 0, 'max' => 100],

            ['payments', 'validatePayments'],

            [['division_id'], 'exist', 'skipOnError' => false, 'targetClass' => Division::className(), 'targetAttribute' => ['division_id' => 'id']],
            [['company_cash_id'], 'exist', 'skipOnError' => false, 'targetClass' => CompanyCash::className(), 'targetAttribute' => ['company_cash_id' => 'id']],
            [['staff_id'], 'exist', 'skipOnError' => false, 'targetClass' => Staff::className(), 'targetAttribute' => ['staff_id' => 'id']],
        ];
    }

    public function validatePayments($attribute, $params)
    {
        if (array_sum($this->payments) == 0 && $this->order_total_price > 0) {
            $this->payments[key($this->payments)] = $this->order_total_price;
        } else if ($this->order_total_price != array_sum($this->payments)) {
            $this->addError('price', Yii::t('app', 'Setup payments'));
        }
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'status' => Yii::t('app', 'Status'),
            'company_customer_id' => Yii::t('app', 'Customer'),
            'created_time' => Yii::t('app', 'Created Time'),
            'discount' => Yii::t('app', 'Services discount, %'),
            'created_user_id' => Yii::t('app', 'Created User'),
            'datetime' => Yii::t('app', 'Datetime'),
            'price' => Yii::t('app', 'Services cost currency'),
            'note' => Yii::t('app', 'Note'),
            'staff_id' => Yii::t('app', 'Staff ID'),
            'division_service_id' => Yii::t('app', 'Division Service ID'),
            'services' => Yii::t('app', 'Services'),
            'color' => Yii::t('app', 'Color'),
            'customer_name' => Yii::t('app', 'Full Name'),
            'customer_lastname' => Yii::t('app', 'Last Name'),
            'customer_phone' => Yii::t('app', 'Customer Phone'),
            'customer_email' => Yii::t('app', 'Customer Email'),
            'division_id' => Yii::t('app', 'Division ID'),
            'notify_status' => Yii::t('app', 'Notify Status'),
            'hours_before' => Yii::t('app', 'Hours Before'),
            'company_cash_id' => Yii::t('app', 'Company Cash'),
            'key' => Yii::t('app', 'Order Key'),
            'payment_amount' => Yii::t('app', 'Payment Amount'),
            'productsPrice' => Yii::t('app', 'Products cost currency'),
            'customer_source_id' => Yii::t('app', 'Customer Source'),
            'referrer_id' => Yii::t('app', 'Referrer'),
        ];
    }

    /**
     * @return string
     */
    public function formName()
    {
        return 'Order';
    }
}