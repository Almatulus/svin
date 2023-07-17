<?php

namespace core\services\order\dto;

/**
 * @property integer $id
 * @property integer $discount
 * @property integer $division_service_id
 * @property integer $price
 * @property integer $duration
 * @property integer $quantity
 */
class OrderServiceData
{
    public $id;
    public $discount;
    public $division_service_id;
    public $price;
    public $duration;
    public $quantity;

    /**
     * OrderServiceData constructor.
     *
     * @param integer      $division_service_id
     * @param integer      $price
     * @param integer      $duration
     * @param integer      $discount
     * @param integer      $quantity
     * @param integer|null $id
     */
    public function __construct(
        $division_service_id,
        $price,
        $duration,
        $discount,
        $quantity,
        $id = null
    ) {
        $this->id                  = $id;
        $this->discount            = $discount;
        $this->division_service_id = $division_service_id;
        $this->price               = $price;
        $this->duration            = $duration;
        $this->quantity            = $quantity;
    }
}