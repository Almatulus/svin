<?php

namespace core\models\finance;

use core\models\warehouse\Product;
use Yii;

/**
 * This is the model class for table "{{%company_cashflow_products}}".
 *
 * @property int $cashflow_id
 * @property int $product_id
 * @property int $price
 * @property int $quantity
 * @property int $discount
 *
 * @property CompanyCashflow $cashflow
 * @property Product $product
 */
class CompanyCashflowProduct extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%company_cashflow_products}}';
    }

    /**
     * @inheritdoc
     * @return \core\models\finance\query\CashflowProductQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \core\models\finance\query\CashflowProductQuery(get_called_class());
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['product_id', 'price', 'quantity'], 'required'],
            [['cashflow_id', 'product_id'], 'default', 'value' => null],
            ['discount', 'default', 'value' => 0],
            ['discount', 'integer', 'min' => 0],
            [['cashflow_id', 'product_id', 'price', 'quantity'], 'integer'],
            [['cashflow_id', 'product_id'], 'unique', 'targetAttribute' => ['cashflow_id', 'product_id']],
            [
                ['cashflow_id'],
                'exist',
                'skipOnError'     => true,
                'targetClass'     => CompanyCashflow::className(),
                'targetAttribute' => ['cashflow_id' => 'id']
            ],
            [
                ['product_id'],
                'exist',
                'skipOnError'     => true,
                'targetClass'     => Product::className(),
                'targetAttribute' => ['product_id' => 'id']
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
            'product_id'  => Yii::t('app', 'Product ID'),
            'price'       => Yii::t('app', 'Price'),
            'quantity'    => Yii::t('app', 'Quantity'),
            'discount'    => Yii::t('app', 'Discount'),
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
    public function getProduct()
    {
        return $this->hasOne(Product::className(), ['id' => 'product_id']);
    }
}
