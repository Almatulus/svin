<?php

namespace modules\order\forms;

use core\forms\order\OrderMoveForm;
use core\models\division\DivisionService;
use core\models\order\Order;
use core\models\order\OrderService;
use core\models\Staff;

class OrderMoveFormTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * @var OrderMoveForm
     */
    private $_form;

    /**
     * @var Order
     */
    private $order;
    /**
     * @var Staff
     */
    private $staffWithServices;
    /**
     * @var Staff
     */
    private $staffWithoutServices;
    /**
     * @var DivisionService
     */
    private $divisionService;

    protected function _before()
    {
        $this->staffWithServices = $this->tester->getFactory()->create(Staff::class);
        $this->staffWithoutServices = $this->tester->getFactory()->create(Staff::class);

        $this->divisionService = $this->tester->getFactory()->create(DivisionService::class);
        $this->staffWithServices->link('divisionServices', $this->divisionService);

        $this->order = $this->tester->getFactory()->create(Order::class);
        $this->order->staff->link('divisionServices', $this->divisionService);

        $this->tester->getFactory()->create(OrderService::class, [
            'order_id'            => $this->order->id,
            'division_service_id' => $this->divisionService->id
        ]);

        $this->_form = new OrderMoveForm($this->order);
    }

    protected function _after()
    {
    }

    // tests
    public function testValidation()
    {
        // test required attributes
        expect("model is not valid", $this->_form->validate())->false();
        expect("staff error", $this->_form->getErrors())->hasKey("staff");
        expect("start error", $this->_form->getErrors())->hasKey("start");

        // validate non-existing staff
        $this->_form->setAttributes([
            'staff' => 0,
            'start' => date("Y-m-d H:i:s")
        ]);
        expect("model is not valid", $this->_form->validate())->false();
        expect("staff error", $this->_form->getErrors())->hasKey("staff");

        // validate incorrect date format
        $this->_form->setAttributes([
            'staff' => $this->staffWithServices->id,
            'start' => date("Y-m-d")
        ]);
        expect("model is not valid", $this->_form->validate())->false();
        expect("start error", $this->_form->getErrors())->hasKey("start");

        // validate staff which does not provide order services
        $this->_form->setAttributes([
            'staff' => $this->staffWithoutServices->id,
            'start' => date("Y-m-d H:i:s")
        ]);
        expect("model is not valid", $this->_form->validate())->false();
        expect("staff error", $this->_form->getErrors())->hasKey("staff");

        // validate correct values
        $this->_form->setAttributes([
            'staff' => $this->staffWithServices->id,
            'start' => date("Y-m-d H:i:s")
        ]);
        expect("model is not valid", $this->_form->validate())->true();

    }
}