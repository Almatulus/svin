<?php

namespace core\services\dto;

/**
 * Class ProductData
 * @package core\services\dto
 */
class ProductData
{
    public $product_id;
    public $discount;
    public $price;
    public $quantity;

    /**
     * ProductData constructor.
     * @param $product_id
     * @param $discount
     * @param $price
     * @param $quantity
     */
    public function __construct(int $product_id, int $price, int $quantity = 1, $discount = 0)
    {
        $this->product_id = $product_id;
        $this->price = $price;
        $this->quantity = $quantity;
        $this->discount = $discount;
    }

    /**
     * @return float|int
     */
    public function getSum()
    {
        return ($this->price * $this->quantity) * (100 - $this->discount) / 100;
    }
}