<?php

namespace core\calculators;

class ProductSellingCalculator implements IProductCalculator
{
    private $product;

    public function __construct(IProduct $product, IProductCalculator $calculator = null)
    {
        $this->product = $product;
    }

    function calculate()
    {
        return $this->product->getPrice();
    }
}