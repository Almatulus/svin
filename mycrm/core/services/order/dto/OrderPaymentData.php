<?php

namespace core\services\order\dto;

/**
 * @property $payment_id;
 * @property $amount;
 */
class OrderPaymentData
{
    public $payment_id;
    public $amount;
    public $is_accountable;

    public function __construct($payment_id, $amount, $is_accountable = true)
    {
        $this->payment_id = $payment_id;
        $this->amount = $amount;
        $this->is_accountable = $is_accountable;
    }
}