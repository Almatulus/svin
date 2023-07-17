<?php

namespace modules\warehouse\forms;

use core\forms\warehouse\stocktake\StocktakeCreateForm;
use core\models\division\Division;
use core\models\user\User;
use core\models\warehouse\Category;

class StocktakeCreateFormTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * @var StocktakeCreateForm
     */
    protected $form;

    public function testValidation()
    {
        verify("Creator is not required", $this->form->validate(['creator_id'], true))->false();
        verify("Division is required", $this->form->validate(['division_id'], true))->false();
        verify("Name is required", $this->form->validate(['name'], true))->false();
        verify("Category is not required by default", $this->form->validate(['category_id'], true))->true();

        $user = $this->tester->getFactory()->create(User::class);

        $division = $this->tester->getFactory()->create(Division::class, [
            'company_id' => $user->company_id
        ]);

        $category = $this->tester->getFactory()->create(Category::class, [
            'company_id' => $user->company_id
        ]);

        $this->form->setAttributes([
            'division_id' => $division->id,
            'name' => $this->tester->getFaker()->name,
            'creator_id' => $user->id,
            'category_id' => $category->id
        ]);

        verify("Creator exists", $this->form->validate(['creator_id'], true))->true();
        verify("Division is filled", $this->form->validate(['division_id'], true))->true();
        verify("Name is filled", $this->form->validate(['name'], true))->true();
        verify("Category exists", $this->form->validate(['category_id'], true))->true();
    }


    // tests

    protected function _before()
    {
        $this->form = new StocktakeCreateForm();
    }

    protected function _after()
    {
    }
}