<?php

namespace app\tests\unit\modules\finance\services;

use core\forms\finance\CashflowForm;
use core\forms\finance\CashflowUpdateForm;
use core\models\customer\CompanyCustomer;
use core\models\division\Division;
use core\models\division\DivisionPayment;
use core\models\finance\CompanyCash;
use core\models\finance\CompanyCashflow;
use core\models\finance\CompanyCashflowPayment;
use core\models\finance\CompanyCostItem;
use core\models\Payment;
use core\models\Staff;
use core\models\user\User;
use core\services\CompanyCashflowService;

class CompanyCashflowServiceTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * @var CompanyCashflowService
     */
    private $cashFlowService;

    /**
     * @var CompanyCash
     */
    private $companyCash;
    /**
     * @var CompanyCostItem
     */
    private $costItem;
    /**
     * @var Division
     */
    private $division;
    /**
     * @var Payment
     */
    private $payment;
    /**
     * @var User
     */
    private $user;

    /**
     * @throws \yii\base\InvalidConfigException
     */
    protected function _before()
    {
        $this->cashFlowService = \Yii::createObject(CompanyCashflowService::class);

        $this->user = $this->tester->getFactory()->create(User::class);
        $this->division = $this->tester->getFactory()->create(Division::class, [
            'company_id' => $this->user->company_id
        ]);
        $this->companyCash = $this->tester->getFactory()->create(CompanyCash::class, [
            'division_id' => $this->division->id
        ]);
        $this->costItem = $this->tester->getFactory()->create(CompanyCostItem::class, [
            'company_id' => $this->division->company_id
        ]);
        $this->payment = $this->tester->getFactory()->create(Payment::class);
        $this->tester->getFactory()->create(DivisionPayment::class, [
            'division_id' => $this->division->id,
            'payment_id'  => $this->payment->id
        ]);

        \Yii::$app->set('user', $this->user);
    }

    public function _after()
    {

    }

    // tests
    public function testAdd()
    {
        $form = new CashflowForm($this->user->id);
        $form->setAttributes([
            'date'          => date("Y-m-d H:i:s"),
            'cash_id'       => $this->companyCash->id,
            'comment'       => null,
            'contractor_id' => null,
            'cost_item_id'  => $this->costItem->id,
            'customer_id'   => null,
            'division_id'   => $this->division->id,
            'receiver_mode' => CompanyCashflow::RECEIVER_CONTRACTOR,
            'staff_id'      => null,
            'value'         => $this->tester->getFaker()->randomNumber(3),
            'payments'      => [
                [
                    'payment_id' => $this->payment->id,
                    'value'      => $this->tester->getFaker()->randomNumber(2)
                ]
            ]
        ]);
        $cashflow = $this->cashFlowService->add($form);

        $attribute_keys = array_diff(array_keys($form->getAttributes()), ['payments']);

        expect("Cashflow Model", $cashflow)->isInstanceOf(CompanyCashflow::class);
        expect("Cashflow Model id is not empty", $cashflow->id)->notNull();
        expect($cashflow->getAttributes($attribute_keys))->equals($form->getAttributes($attribute_keys));

        $this->tester->canSeeRecord(CompanyCashflowPayment::class, [
            'cashflow_id' => $cashflow->id,
            'payment_id'  => $this->payment->id,
            'value'       => $form->payments[0]['value']
        ]);
    }

    public function testEdit()
    {
        $cashflow = $this->tester->getFactory()->create(CompanyCashflow::class, [
            'division_id' => $this->division->id,
            'company_id'  => $this->division->company_id,
            'user_id'     => $this->user->id
        ]);
        $companyCustomer = $this->tester->getFactory()->create(CompanyCustomer::class, [
            'company_id' => $this->division->company_id,
        ]);
        $staff = $this->tester->getFactory()->create(Staff::class);

        $form = new CashflowUpdateForm($cashflow->id, $this->user->id);

        $form->setAttributes([
            'date'          => date("Y-m-d H:i:s"),
            'cash_id'       => $this->companyCash->id,
            'comment'       => null,
            'contractor_id' => null,
            'cost_item_id'  => $this->costItem->id,
            'customer_id'   => $companyCustomer->id,
            'division_id'   => $this->division->id,
            'receiver_mode' => CompanyCashflow::RECEIVER_CUSTOMER,
            'staff_id'      => $staff->id,
            'value'         => $this->tester->getFaker()->randomNumber(3),
            'payments'      => [
                [
                    'payment_id' => $this->payment->id,
                    'value'      => $this->tester->getFaker()->randomNumber(2)
                ]
            ]
        ]);

        $cashflow = $this->cashFlowService->add($form);

        $attribute_keys = array_diff(array_keys($form->getAttributes()), ['payments']);

        expect("Cashflow Model", $cashflow)->isInstanceOf(CompanyCashflow::class);
        expect("Cashflow Model id is not empty", $cashflow->id)->notNull();
        expect($cashflow->getAttributes($attribute_keys))->equals($form->getAttributes($attribute_keys));

        $this->tester->canSeeRecord(CompanyCashflowPayment::class, [
            'cashflow_id' => $cashflow->id,
            'payment_id'  => $this->payment->id,
            'value'       => $form->payments[0]['value']
        ]);
    }
}
