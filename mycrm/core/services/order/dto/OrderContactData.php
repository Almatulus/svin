<?php

namespace core\services\order\dto;

/**
 * @property integer $id
 * @property integer $name
 * @property integer $phone
 */
class OrderContactData
{
    public $id;
    public $name;
    public $phone;

    /**
     * OrderServiceData constructor.
     *
     * @param integer $id
     * @param integer $phone
     * @param integer $name
     */
    public function __construct($id, $phone, $name)
    {
        $this->id    = $id;
        $this->name  = $name;
        $this->phone = $phone;
    }
}