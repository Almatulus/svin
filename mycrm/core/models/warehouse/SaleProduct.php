<?php

namespace core\models\warehouse;

use Yii;

/**
 * This is the model class for table "{{%warehouse_sale_product}}".
 *
 * @property integer $id
 * @property double $quantity
 * @property double $price
 * @property double $purchase_price
 * @property integer $product_id
 * @property integer $sale_id
 * @property integer $discount
 *
 * @property Product $product
 * @property Sale $sale
 */
class SaleProduct extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%warehouse_sale_product}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['quantity', 'product_id', 'price'], 'required'],
            [['price', 'purchase_price'], 'number'],

            ['discount', 'default', 'value' => 0],
            ['discount', 'integer', 'min' => 0, 'max' => 100],

            ['quantity', 'number', 'min' => 1],
            [['product_id', 'sale_id'], 'integer'],
            [
                ['product_id'],
                'exist',
                'skipOnError'     => true,
                'targetClass'     => Product::className(),
                'targetAttribute' => ['product_id' => 'id']
            ],
            [
                ['sale_id'],
                'exist',
                'skipOnError'     => true,
                'targetClass'     => Sale::className(),
                'targetAttribute' => ['sale_id' => 'id']
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'              => Yii::t('app', 'ID'),
            'quantity'        => Yii::t('app', 'Quantity'),
            'extraCharge'     => Yii::t('app', 'Extra charge'),
            'extraChargeRate' => Yii::t('app', 'Extra charge, %'),
            'income'          => Yii::t('app', 'Income'),
            'price'           => Yii::t('app', 'Selling price'),
            'purchase_price'  => Yii::t('app', 'Purchase price'),
            'product_id'      => Yii::t('app', 'Product'),
            'sale_id'         => Yii::t('app', 'Sale ID'),
            'totalCost'       => Yii::t('app', 'Total cost'),
            'discount'        => Yii::t('app', 'Discount'),
        ];
    }

    public function init()
    {
        $this->on(self::EVENT_BEFORE_INSERT, [$this, 'setDefaultPurchasePrice']);
        $this->on(self::EVENT_BEFORE_UPDATE, [$this, 'setDefaultPurchasePrice']);
    }

    public function beforeDelete()
    {
        if (!parent::beforeDelete()) {
            return false;
        }


        return true;
    }

    public function setDefaultPurchasePrice()
    {
        $this->purchase_price = $this->getProduct()->select('purchase_price')
            ->where(['id' => $this->product_id])->scalar();
    }

    /**
     * @return float
     */
    public function getExtraCharge()
    {
        return $this->price - $this->purchase_price;
    }

    /**
     * @return float|null
     */
    public function getExtraChargeRate()
    {
        return $this->purchase_price ? ($this->extraCharge / $this->purchase_price) : 1;
    }

    /**
     * @return float
     */
    public function getFinalPrice()
    {
        return round((100 - $this->discount) / 100 * $this->price * $this->quantity);
    }

    /**
     * @return float
     */
    public function getTotalCost()
    {
        return ($this->price + $this->purchase_price) * $this->quantity;
    }

    /**
     * @return mixed
     */
    public function getIncome()
    {
        return $this->extraCharge * $this->quantity;
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
    public function getSale()
    {
        return $this->hasOne(Sale::className(), ['id' => 'sale_id']);
    }
}
