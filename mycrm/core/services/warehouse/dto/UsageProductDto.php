<?php

namespace core\services\warehouse\dto;

class UsageProductDto
{
    /**
     * @var integer
     */
    private $product_id;

    /**
     * @var integer
     */
    private $quantity;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $purchase_price;

    /**
     * @var integer
     */
    private $selling_price;

    public function __construct(int $product_id, int $quantity, int $id = null)
    {
        $this->product_id = $product_id;
        $this->quantity = $quantity;
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getProductId(): int
    {
        return $this->product_id;
    }

    /**
     * @return int
     */
    public function getQuantity(): int
    {
        return $this->quantity;
    }

    /**
     * @return int|null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getPurchasePrice()
    {
        return $this->purchase_price;
    }

    /**
     * @param mixed $purchase_price
     */
    public function setPurchasePrice($purchase_price)
    {
        $this->purchase_price = $purchase_price;
    }

    /**
     * @return mixed
     */
    public function getSellingPrice()
    {
        return $this->selling_price;
    }

    /**
     * @param mixed $selling_price
     */
    public function setSellingPrice($selling_price)
    {
        $this->selling_price = $selling_price;
    }
}
