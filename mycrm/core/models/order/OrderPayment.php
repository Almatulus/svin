<?php

namespace core\models\order;

use common\components\HistoryBehavior;
use core\models\order\query\OrderPaymentQuery;
use core\models\Payment;
use Yii;

/**
 * This is the model class for table "{{%order_payments}}".
 *
 * @property integer $id
 * @property integer $order_id
 * @property integer $payment_id
 * @property integer $amount
 *
 * @property Order $order
 * @property Payment $payment
 */
class OrderPayment extends \yii\db\ActiveRecord
{
    /**
     * @param Order $order
     * @param Payment $payment
     * @param integer $amount
     * @return OrderPayment
     */
    public static function add(Order $order, Payment $payment, $amount)
    {
        $model = new self();
        $model->populateRelation('order', $order);
        $model->populateRelation('payment', $payment);
        $model->amount = $amount;
        return $model;
    }

    /**
     * @param integer $amount
     */
    public function edit($amount)
    {
        $this->amount = $amount;
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            $related = $this->getRelatedRecords();
            /** @var Order $order */
            if (isset($related['order']) && $order = $related['order']) {
                $order->save();
                $this->order_id = $order->id;
            }
            /** @var Payment $payment */
            if (isset($related['payment']) && $payment = $related['payment']) {
                $payment->save();
                $this->payment_id = $payment->id;
            }
            return true;
        }
        return false;
    }

    /**
     * @inheritdoc
     */
    public function fields()
    {
        return [
            'name' => function () {
                return Yii::t('app', $this->payment->name);
            },
            'payment_id',
            'amount',
            'order_id',
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%order_payments}}';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrder()
    {
        return $this->hasOne(Order::className(), ['id' => 'order_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPayment()
    {
        return $this->hasOne(Payment::className(), ['id' => 'payment_id']);
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            HistoryBehavior::className(),
        ];
    }

    /**
     * @inheritdoc
     */
    public static function find()
    {
        return new OrderPaymentQuery(get_called_class());
    }
}
