<?php

namespace core\calculators;

class CompanyReverseProductCalculator implements IProductCalculator
{
    private $product;
    private $prevCalculator;

    public function __construct(IProduct $product, IProductCalculator $calculator)
    {
        $this->prevCalculator = $calculator;
        $this->product = $product;
    }

    function calculate()
    {
        return $this->prevCalculator->calculate($this->product) * -1;
    }
}