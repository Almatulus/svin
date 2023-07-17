<?php

namespace core\models\division;

use core\models\Payment;
use Yii;

/**
 * This is the model class for table "{{%division_payments}}".
 *
 * @property integer $division_id
 * @property integer $payment_id
 * @property integer $status
 *
 * @property Division $division
 * @property Payment $payment
 */
class DivisionPayment extends \yii\db\ActiveRecord
{
    /**
     * @const
     */
    const STATUS_DISABLED = 0;
    const STATUS_ENABLED = 1;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%division_payments}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['division_id', 'payment_id'], 'required'],
            [['division_id', 'payment_id'], 'integer']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'division_id' => Yii::t('app', 'Division ID'),
            'payment_id' => Yii::t('app', 'Payment ID'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDivision()
    {
        return $this->hasOne(Division::className(), ['id' => 'division_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPayment()
    {
        return $this->hasOne(Payment::className(), ['id' => 'payment_id']);
    }

    /**
     * Enable
     */
    public function enable()
    {
        $this->status = self::STATUS_ENABLED;
    }

    /**
     * Disable
     */
    public function disable()
    {
        $this->status = self::STATUS_DISABLED;
    }

    /**
     * @param $division_id
     * @param $payment_id
     * @return DivisionPayment
     */
    public static function add($division_id, $payment_id)
    {
        $payment = new self();
        $payment->division_id = $division_id;
        $payment->payment_id = $payment_id;
        $payment->status = self::STATUS_ENABLED;
        return $payment;
    }

    public function fields()
    {
        return [
            'id' => 'payment_id',
            'name' => function(self $model) {
                return $model->payment->getLabel();
            }
        ];
    }

}
