<?php

namespace modules\order\forms;

use core\forms\order\OrderDocumentForm;
use core\models\order\Order;
use core\models\order\OrderDocumentTemplate;


class OrderDocumentFormTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * @var OrderDocumentForm
     */
    private $_form;

    /**
     * @var Order
     */
    private $order;
    /**
     * @var OrderDocumentTemplate
     */
    private $template;

    protected function _before()
    {
        if (!$this->order) {
            $this->order = $this->tester->getFactory()->create(Order::class);
        }

        if (!$this->template) {
            $this->template = $this->tester->getFactory()->create(OrderDocumentTemplate::class, [
                'company_id'  => $this->order->division->company_id,
                'category_id' => $this->order->division->company->category_id
            ]);
        }

        $this->_form = new OrderDocumentForm();
    }

    protected function _after()
    {
    }

    // tests
    public function testValidation()
    {
        expect("model is not valid", $this->_form->validate())->false();
        expect("order_id error", $this->_form->getErrors())->hasKey("order_id");
        expect("template_id error", $this->_form->getErrors())->hasKey("template_id");

        // validate non-existing order
        $this->_form->setAttributes([
            'order_id'    => 0,
            'template_id' => $this->template->id
        ]);
        expect("model is not valid", $this->_form->validate())->false();
        expect("order_id error", $this->_form->getErrors())->hasKey("order_id");


        // validate non-existing template
        $this->_form->setAttributes([
            'order_id'    => $this->order->id,
            'template_id' => 0
        ]);
        expect("model is not valid", $this->_form->validate())->false();
        expect("template_id error", $this->_form->getErrors())->hasKey("template_id");

        // validate correct values
        $this->_form->setAttributes([
            'order_id'    => $this->order->id,
            'template_id' => $this->template->id
        ]);
        expect("model is valid", $this->_form->validate())->true();
    }
}