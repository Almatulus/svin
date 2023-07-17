<?php

namespace core\calculators;

interface IProductCalculator
{
    public function __construct(IProduct $product, IProductCalculator $calculator);
    public function calculate();
}