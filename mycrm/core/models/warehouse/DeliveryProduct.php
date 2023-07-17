<?php

namespace core\models\warehouse;

use Yii;

/**
 * This is the model class for table "{{%warehouse_delivery_product}}".
 *
 * @property integer $id
 * @property double $quantity
 * @property double $price
 * @property integer $product_id
 * @property integer $delivery_id
 *
 * @property Delivery $delivery
 * @property Product $product
 */
class DeliveryProduct extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%warehouse_delivery_product}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['quantity', 'price', 'product_id'], 'required'],
            [['price'], 'number'],
            ['quantity', 'number', 'min' => 1],
            [['product_id', 'delivery_id'], 'integer'],
            [['delivery_id'], 'exist', 'skipOnError' => true, 'targetClass' => Delivery::className(), 'targetAttribute' => ['delivery_id' => 'id']],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => Product::className(), 'targetAttribute' => ['product_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'quantity' => Yii::t('app', 'Quantity'),
            'price' => Yii::t('app', 'Price'),
            'product_id' => Yii::t('app', 'Product'),
            'delivery_id' => Yii::t('app', 'Delivery ID'),
            'sum' => Yii::t('app', 'Sum')
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDelivery()
    {
        return $this->hasOne(Delivery::className(), ['id' => 'delivery_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProduct()
    {
        return $this->hasOne(Product::className(), ['id' => 'product_id']);
    }

    /**
     * @return float
     */
    public function getSum()
    {
        return $this->price * $this->quantity;
    }

    /**
     * @param DeliveryProduct[] $products
     * @return float|int
     */
    public static function getTotalCost($products)
    {
        $total = 0;
        foreach ($products as $key => $product) {
            $total += round($product->price * $product->quantity, 2);
        }
        return $total;
    }
}
