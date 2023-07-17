<?php

namespace core\forms\warehouse;

use core\models\warehouse\Sale;
use Yii;

class SaleUpdateForm extends SaleForm
{
    public $sale;

    /**
     * SaleUpdateForm constructor.
     * @param Sale $sale
     * @param array $config
     */
    public function __construct(Sale $sale, $config = [])
    {
        $this->sale = $sale;
        $this->attributes = $sale->attributes;
        parent::__construct($config);
    }

}
