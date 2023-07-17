<?php

namespace modules\finance\forms;


use core\forms\finance\CashflowForm;
use core\models\division\Division;
use core\models\finance\CompanyCash;
use core\models\finance\CompanyCostItem;
use core\models\user\User;

class CashflowFormTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * @var CashflowForm
     */
    protected $form;

    protected $costItem;
    protected $division;
    protected $cash;
    /**
     * @var User
     */
    protected $user;

    public function testValidateRequired()
    {
        $this->form->date = null;
        $this->form->validate();

        verify("date is required", $this->form->errors)->hasKey('date');
        verify("cost_item_id is required", $this->form->errors)->hasKey('cost_item_id');
        verify("cash_id is required", $this->form->errors)->hasKey('cash_id');
        verify("division_id is required", $this->form->errors)->hasKey('division_id');
        verify("value is required", $this->form->errors)->hasKey('value');

        $this->form->setAttributes([
            'date'         => date("Y-m-d H:i"),
            'cost_item_id' => $this->costItem->id,
            'cash_id'      => $this->cash->id,
            'division_id'  => $this->division->id,
            'value'        => $this->tester->getFaker()->randomNumber(3)
        ]);
        $this->form->validate();

        verify("date is valid", $this->form->errors)->hasntKey('date');
        verify("cost_item_id is valid", $this->form->errors)->hasntKey('cost_item_id');
        verify("cash_id is valid", $this->form->errors)->hasntKey('cash_id');
        verify("division_id is valid", $this->form->errors)->hasntKey('division_id');
        verify("value is valid", $this->form->errors)->hasntKey('value');
    }

    protected function _before()
    {
        $this->user = $this->tester->getFactory()->create(User::class);
        $this->costItem = CompanyCostItem::find()->company($this->user->company_id)->orderBy('id')->one();
        $this->division = $this->tester->getFactory()->create(Division::class,
            ['company_id' => $this->user->company_id]);
        $this->cash = $this->tester->getFactory()->create(CompanyCash::class, ['division_id' => $this->division->id]);
        $this->form = new CashflowForm($this->user->id);
    }

    // tests

    protected function _after()
    {
    }
}