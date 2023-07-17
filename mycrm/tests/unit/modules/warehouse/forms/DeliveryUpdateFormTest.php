<?php

namespace modules\warehouse\forms;

use core\forms\warehouse\delivery\DeliveryUpdateForm;
use core\models\division\Division;
use core\models\finance\CompanyContractor;
use core\models\warehouse\Delivery;

class DeliveryUpdateFormTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * @var DeliveryUpdateForm
     */
    protected $form;

    public function testValidation()
    {
        // unset required data
        $this->form->setAttributes([
            'delivery_date' => '',
            'division_id' => null
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
        /**
         * @var Delivery $delivery
         */
        $delivery = $this->tester->getFactory()->create(Delivery::class);
        $this->form = new DeliveryUpdateForm($delivery);
    }

    protected function _after()
    {
    }
}