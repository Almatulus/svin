<?php

namespace modules\warehouse\forms;


use Codeception\Util\Stub;
use core\forms\warehouse\product\ProductCreateForm;
use core\models\warehouse\Product;
use core\models\warehouse\ProductType;

class ProductCreateFormTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * @var ProductCreateForm
     */
    protected $form;

    public function testValidateName()
    {
        verify("Name is required", $this->form->validate(['name'], true))->false();

        $this->form->name = 1;
        verify("Name is string, integer given", $this->form->validate(['name'], true))->false();

        $this->form->name = false;
        verify("Name is string, boolean given", $this->form->validate(['name'], true))->false();

        $this->form->name = $this->tester->getFaker()->name;
        verify("Name is valid", $this->form->validate(['name'], true))->true();
    }

    public function testValidateTypes()
    {
        $type = $this->tester->getFactory()->create(ProductType::class);

        $this->form->types = ['asd'];
        verify("Types has to be integer", $this->form->validate(['types'], true))->false();

        $this->form->types = [0];
        verify("Types must exist", $this->form->validate(['types'], true))->false();

        $this->form->types = null;
        verify("Empty types are allowed", $this->form->validate(['types'], true))->true();

        $this->form->types = [$type->id];
        verify("Types are valid", $this->form->validate(['types'], true))->true();
    }

    // tests

    protected function _before()
    {
        $this->form = Stub::make(ProductCreateForm::class);
    }

    protected function _after()
    {
    }
}