<?php

namespace core\forms\finance;

use core\models\finance\Payroll;
use Yii;

class PayrollUpdateForm extends PayrollForm
{
    public $payroll;

    /**
     * PayrollUpdateForm constructor.
     * @param Payroll $payroll
     * @param array $config
     */
    public function __construct(Payroll $payroll, $config = [])
    {
        $this->payroll = $payroll;
        $this->attributes = $payroll->attributes;
        parent::__construct($config);
    }

}
