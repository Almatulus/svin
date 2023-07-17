<?php

namespace core\services\dto;

class ServiceData
{
    public $discount;
    public $price;
    public $quantity;
    public $service_id;

    /**
     * ServiceData constructor.
     * @param $discount
     * @param $price
     * @param $quantity
     * @param $service_id
     */
    public function __construct(int $price, int $service_id, int $quantity = 1, $discount = 0)
    {
        $this->discount = $discount;
        $this->price = $price;
        $this->quantity = $quantity;
        $this->service_id = $service_id;
    }

    /**
     * @return float|int
     */
    public function getSum()
    {
        return ($this->price) * (100 - $this->discount) / 100;
    }
}