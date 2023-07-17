<?php

namespace core\services\dto;

/**
 * @property integer $tariff_id
 * @property integer $balance
 */

class CompanyPaymentData
{
    public $tariff_id;

    public function __construct($tariff_id)
    {
        $this->tariff_id = $tariff_id;
    }
}
