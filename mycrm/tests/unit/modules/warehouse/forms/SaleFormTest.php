<?php

namespace modules\warehouse\forms;

use core\forms\warehouse\SaleForm;
use core\models\customer\CompanyCustomer;
use core\models\finance\CompanyCash;
use core\models\Payment;
use core\models\Staff;

class SaleFormTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * @var SaleForm
     */
    protected $form;

    public function testValidation()
    {
        $this->form->setAttributes([
            'cash_id' => 0,
            'company_customer_id' => 0,
            'payment_id' => 0,
            'staff_id' => 0,
        ]);

        verify("Sale date is required", $this->form->validate(['sale_date'], true))->true();
        verify("Division is required", $this->form->validate(['division_id'], true))->false();
        verify("Cash doesn't exist", $this->form->validate(['cash_id'], true))->false();
        verify("Company customer doesn't exist", $this->form->validate(['company_customer_id'], true))->false();
        verify("Payment doesn't exist", $this->form->validate(['payment_id'], true))->false();
        verify("Staff doesn't exist", $this->form->validate(['staff_id'], true))->false();

        $cash = $this->tester->getFactory()->create(CompanyCash::class, []);
        $companyCustomer = $this->tester->getFactory()->create(CompanyCustomer::class, []);
        $payment = $this->tester->getFactory()->create(Payment::class, []);
        $staff = $this->tester->getFactory()->create(Staff::class, []);

        $this->form->setAttributes([
            'cash_id' => $cash->id,
            'company_customer_id' => $companyCustomer->id,
            'payment_id' => $payment->id,
            'staff_id' => $staff->id,
        ]);

        verify("Cash exists", $this->form->validate(['cash_id'], true))->true();
        verify("Company customer exists", $this->form->validate(['company_customer_id'], true))->true();
        verify("Payment exists", $this->form->validate(['payment_id'], true))->true();
        verify("Staff exists", $this->form->validate(['staff_id'], true))->true();
    }


    // tests

    protected function _before()
    {
        $this->form = new SaleForm();
    }

    protected function _after()
    {
    }
}