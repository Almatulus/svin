<?php

namespace core\models\warehouse;

use Yii;

/**
 * This is the model class for table "{{%warehouse_use_product}}".
 *
 * @property integer $id
 * @property double $quantity
 * @property double $purchase_price
 * @property double $selling_price
 * @property integer $product_id
 * @property integer $usage_id
 *
 * @property Product $product
 * @property Usage $use
 */
class UsageProduct extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%warehouse_usage_product}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['quantity', 'product_id'], 'required'],
            [['quantity'], 'number', 'min' => 1],
            [['purchase_price', 'selling_price'], 'number'],
            [['product_id', 'usage_id'], 'integer'],
            [
                ['product_id'],
                'exist',
                'skipOnError'     => true,
                'targetClass'     => Product::className(),
                'targetAttribute' => ['product_id' => 'id']
            ],
            [
                ['usage_id'],
                'exist',
                'skipOnError'     => true,
                'targetClass'     => Usage::className(),
                'targetAttribute' => ['usage_id' => 'id']
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'         => Yii::t('app', 'ID'),
            'quantity'   => Yii::t('app', 'Quantity'),
            'product_id' => Yii::t('app', 'Product ID'),
            'usage_id'   => Yii::t('app', 'Use ID'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProduct()
    {
        return $this->hasOne(Product::className(), ['id' => 'product_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUse()
    {
        return $this->hasOne(Usage::className(), ['id' => 'usage_id']);
    }
}
