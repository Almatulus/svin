<?php

namespace core\services\dto;

class PaymentData
{
    public $payment_id;
    public $amount;
    public $is_accountable;

    /**
     * PaymentData constructor.
     * @param int $payment_id
     * @param int $amount
     * @param bool $is_accountable
     */
    public function __construct(int $payment_id, int $amount, bool $is_accountable = true)
    {
        $this->payment_id = $payment_id;
        $this->amount = $amount;
        $this->is_accountable = $is_accountable;
    }
}