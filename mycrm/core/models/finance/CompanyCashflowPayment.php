<?php

namespace core\models\finance;

use core\models\Payment;
use Yii;

/**
 * This is the model class for table "{{%company_cashflow_payments}}".
 *
 * @property int $cashflow_id
 * @property int $payment_id
 * @property int $value
 *
 * @property CompanyCashflow $cashflow
 * @property Payment $payment
 */
class CompanyCashflowPayment extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%company_cashflow_payments}}';
    }

    /**
     * @inheritdoc
     * @return \core\models\finance\query\CashflowPaymentQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \core\models\finance\query\CashflowPaymentQuery(get_called_class());
    }

    /**
     * @param CompanyCashflow $cashflow
     * @param int $payment_id
     * @param int $value
     * @return CompanyCashflowPayment
     */
    public static function add(CompanyCashflow $cashflow, int $payment_id, int $value)
    {
        $model = new self();
        $model->populateRelation('cashflow', $cashflow);
        $model->payment_id = $payment_id;
        $model->value = $value;
        return $model;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['payment_id', 'value'], 'required'],
            [['cashflow_id', 'payment_id', 'value'], 'default', 'value' => null],
            [['cashflow_id', 'payment_id', 'value'], 'integer'],
            [['cashflow_id', 'payment_id'], 'unique', 'targetAttribute' => ['cashflow_id', 'payment_id']],
            [
                ['cashflow_id'],
                'exist',
                'skipOnError'     => true,
                'targetClass'     => CompanyCashflow::class,
                'targetAttribute' => ['cashflow_id' => 'id']
            ],
            [
                ['payment_id'],
                'exist',
                'skipOnError'     => true,
                'targetClass'     => Payment::class,
                'targetAttribute' => ['payment_id' => 'id']
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'cashflow_id' => Yii::t('app', 'Cashflow ID'),
            'payment_id'  => Yii::t('app', 'Payment ID'),
            'value'       => Yii::t('app', 'Value'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCashflow()
    {
        return $this->hasOne(CompanyCashflow::class, ['id' => 'cashflow_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPayment()
    {
        return $this->hasOne(Payment::class, ['id' => 'payment_id']);
    }

    /**
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            $related = $this->getRelatedRecords();
            /** @var CompanyCashflow $cashflow */
            if (isset($related['cashflow']) && $cashflow = $related['cashflow']) {
                if ($cashflow->isNewRecord) {
                    $cashflow->save();
                }
                $this->cashflow_id = $cashflow->id;
            }
            return true;
        }
        return false;
    }
}
