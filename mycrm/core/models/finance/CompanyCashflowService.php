<?php

namespace core\models\finance;

use core\models\division\DivisionService;
use Yii;

/**
 * This is the model class for table "{{%company_cashflow_services}}".
 *
 * @property int $cashflow_id
 * @property int $service_id
 * @property int $discount
 * @property int $price
 * @property int $quantity
 *
 * @property CompanyCashflow $cashflow
 * @property DivisionService $service
 */
class CompanyCashflowService extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%company_cashflow_services}}';
    }

    /**
     * @inheritdoc
     * @return \core\models\finance\query\CashflowServiceQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \core\models\finance\query\CashflowServiceQuery(get_called_class());
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['service_id', 'discount', 'price', 'quantity'], 'required'],
            [['cashflow_id', 'service_id'], 'default', 'value' => null],
            ['discount', 'default', 'value' => 0],
            ['discount', 'integer', 'min' => 0],
            [['cashflow_id', 'service_id', 'discount', 'price', 'quantity'], 'integer'],
            [
                ['cashflow_id'],
                'exist',
                'skipOnError'     => true,
                'targetClass'     => CompanyCashflow::className(),
                'targetAttribute' => ['cashflow_id' => 'id']
            ],
            [
                ['service_id'],
                'exist',
                'skipOnError'     => true,
                'targetClass'     => DivisionService::className(),
                'targetAttribute' => ['service_id' => 'id']
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
            'service_id'  => Yii::t('app', 'Service ID'),
            'discount'    => Yii::t('app', 'Discount'),
            'price'       => Yii::t('app', 'Price'),
            'quantity'    => Yii::t('app', 'Quantity'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCashflow()
    {
        return $this->hasOne(CompanyCashflow::className(), ['id' => 'cashflow_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getService()
    {
        return $this->hasOne(DivisionService::className(), ['id' => 'service_id']);
    }
}
