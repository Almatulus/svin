<?php

namespace core\models\order;

use common\components\HistoryBehavior;
use core\calculators\IProduct;
use core\models\finance\CompanyCashflowProduct;
use core\models\warehouse\Product;
use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%order_service_products}}".
 *
 * @property integer $id
 * @property integer $order_id
 * @property integer $product_id
 * @property double $selling_price
 * @property double $purchase_price
 * @property double $quantity
 * @property string $deleted_time
 *
 * @property Order $order
 * @property Product $product
 */
class OrderProduct extends ActiveRecord implements IProduct
{
    /**
     * @param Order $order
     * @param Product $product
     * @param integer $quantity
     * @param integer $purchase_price
     * @param integer $selling_price
     * @return OrderProduct
     */
    public static function add(
        Order $order,
        Product $product,
        $quantity,
        $purchase_price,
        $selling_price
    ) {
        $model = new self();
        $model->populateRelation('order', $order);
        $model->populateRelation('product', $product);
        $model->selling_price = $selling_price;
        $model->purchase_price = $purchase_price;
        $model->quantity = $quantity;
        $model->deleted_time = null;
        return $model;
    }

    /**
     * @param integer $quantity
     * @param integer $purchase_price
     * @param integer $selling_price
     */
    public function edit($quantity, $purchase_price, $selling_price)
    {
        $this->selling_price = $selling_price;
        $this->purchase_price = $purchase_price;
        $this->quantity = $quantity;
    }

    public function revertDeletion()
    {
        $this->deleted_time = null;
    }

    /**
     * @param int $discount selling discount
     * @return float
     */
    public function getMargin($discount = 0)
    {
        return ($this->selling_price - $this->purchase_price) * $this->quantity * (100 - $discount) / 100;
    }

    /**
     * Returns total price
     * @return integer
     */
    public function getTotalSellingPrice()
    {
        return $this->selling_price * $this->quantity;
    }

    /**
     * @return string
     */
    public function getCategoryName()
    {
        return $this->product->category->name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->product->name;
    }

    /**
     * @return string
     */
    public function getCashflowComment(): string
    {
        return Yii::t('app', 'Order {order_key}: {item_name}', [
            'order_key' => $this->order->number,
            'item_name' => $this->getName(),
        ]);
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%order_service_products}}';
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'         => Yii::t('app', 'ID'),
            'order_id'   => Yii::t('app', 'Order ID'),
            'product_id' => Yii::t('app', 'Product ID'),
            'quantity'   => Yii::t('app', 'Quantity'),
        ];
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
    public function getProduct()
    {
        return $this->hasOne(Product::className(), ['id' => 'product_id']);
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
            /** @var Product $product */
            if (isset($related['product']) && $product = $related['product']) {
                $product->save();
                $this->product_id = $product->id;
            }
            return true;
        }
        return false;
    }

    /**
     * @return int
     */
    public function getPrice(): int
    {
        return $this->selling_price;
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
     * @return array
     */
    public function fields()
    {
        return [
            'id',
            'name'        => function () {
                return $this->product->name;
            },
            'product_id',
            'quantity',
            'order_id',
            'price'       => 'selling_price',
            'purchase_price',
            'unit'        => function () {
                return $this->product->unit->name;
            },
            'stock_level' => function () {
                return $this->product->quantity;
            }
        ];
    }
}
