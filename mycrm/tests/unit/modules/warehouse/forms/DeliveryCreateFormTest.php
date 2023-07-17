<?php

namespace modules\warehouse\forms;

use core\forms\warehouse\delivery\DeliveryCreateForm;
use core\models\division\Division;
use core\models\finance\CompanyContractor;

class DeliveryCreateFormTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * @var DeliveryCreateForm
     */
    protected $form;

    public function testValidation()
    {
        $this->form->setAttributes([
            'delivery_date' => '',
        ]);

        verify("Contractor is not required", $this->form->validate(['contractor_id'], true))->true();
        verify("Division is required", $this->form->validate(['division_id'], true))->false();
        verify("Delivery date is required", $this->form->validate(['delivery_date'], true))->false();

        $contractor = $this->tester->getFactory()->create(CompanyContractor::class, []);
        $division = $this->tester->getFactory()->create(Division::class, []);

        $this->form->setAttributes([
            'contractor_id' => $contractor->id,
            'division_id' => $division->id,
            'delivery_date' => date('Y-m-d'),
        ]);

        verify("Contractor exists", $this->form->validate(['contractor_id'], true))->true();
        verify("Division is filled", $this->form->validate(['division_id'], true))->true();
        verify("Delivery date is filled", $this->form->validate(['delivery_date'], true))->true();
    }


    // tests

    protected function _before()
    {
        $this->form = new DeliveryCreateForm();
    }

    protected function _after()
    {
    }
}