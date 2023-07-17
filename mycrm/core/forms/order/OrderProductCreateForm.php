<?php

namespace core\forms\order;

use core\models\warehouse\Product;
use yii\base\Model;

/**
 * @property $product_id;
 * @property $quantity;
 * @property $price;
 */
class OrderProductCreateForm extends Model
{
    public $price;
    public $quantity;
    public $product_id;

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['product_id', 'quantity', 'price'], 'required'],
            [['product_id', 'quantity'], 'integer', 'min' => 0],
            ['price', 'integer'],

            [['product_id'], 'exist', 'skipOnError' => false, 'targetClass' => Product::className(), 'targetAttribute' => ['product_id' => 'id']],
        ];
    }
}
