<?php

namespace core\services\order\dto;

/**
 * @property integer $product_id;
 * @property integer $selling_price;
 * @property integer $quantity;
 */
class ProductData
{
    public $product_id;
    public $selling_price;
    public $quantity;

    public function __construct($product_id, $quantity, $selling_price)
    {
        $this->product_id = $product_id;
        $this->selling_price = $selling_price;
        $this->quantity = $quantity;
    }
}