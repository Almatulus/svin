<?php

namespace core\models;

use core\helpers\company\PaymentHelper;
use core\models\finance\CompanyCashflowPayment;
use core\models\order\OrderPayment;
use Yii;

/**
 * This is the model class for table "{{%payments}}".
 *
 * @property integer $id
 * @property string  $name
 * @property integer $type
 * @property integer $status
 */
class Payment extends \yii\db\ActiveRecord
{
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;

    const CASH_ID = 1;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%payments}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['status', 'type'], 'integer'],
            [['name'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'     => Yii::t('app', 'ID'),
            'name'   => Yii::t('app', 'Name'),
            'status' => Yii::t('app', 'Status'),
        ];
    }

    /**
     * Returns payments list
     *
     * @return array
     */
    public static function getPaymentsList()
    {
        $payments = Payment::findAll(['status' => Payment::STATUS_ENABLED]);

        $result = [];
        foreach ($payments as $payment) {
            if (!$payment->isDeposit()) {
                $result[$payment->id] = Yii::t('app', $payment->name);
            }
        }

        return $result;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrderPayments()
    {
        return $this->hasMany(OrderPayment::class, ['payment_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCashflowPayments()
    {
        return $this->hasMany(CompanyCashflowPayment::class, ['payment_id' => 'id']);
    }

    /**
     * Returns title
     *
     * @return string
     */
    public function getLabel()
    {
        return Yii::t('app', $this->name);
    }

    /**
     * @return bool
     */
    public function isCashBack(): bool
    {
        return $this->type === PaymentHelper::CASHBACK;
    }

    /**
     * @return bool
     */
    public function isInsurance(): bool
    {
        return $this->type === PaymentHelper::INSURANCE;
    }

    /**
     * @return bool
     */
    public function isDeposit(): bool
    {
        return $this->type === PaymentHelper::DEPOSIT;
    }

    /**
     * @return bool
     */
    public function isCash(): bool
    {
        return $this->id == PaymentHelper::CASH_ID;
    }

    /**
     * @return bool
     */
    public function isCard(): bool
    {
        return $this->id == PaymentHelper::CARD_ID;
    }

    /**
     * @return bool
     */
    public function isCertificate(): bool
    {
        return $this->type == PaymentHelper::CERTIFICATE;
    }

    /**
     * @return bool
     */
    public function isAccountable(): bool
    {
        return !in_array($this->type, PaymentHelper::notAccountable());
    }

    public function fields()
    {
        return [
            'id'   => 'id',
            'name' => function (Payment $model) {
                return $model->getLabel();
            },
            'type'
        ];
    }
}
