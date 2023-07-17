<?php

namespace forms;

use core\forms\staff\StaffCreateForm;
use core\models\company\Company;
use core\models\company\Tariff;
use core\models\division\Division;
use core\models\Staff;
use core\models\user\User;

class StaffCreateFormTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * @var StaffCreateForm
     */
    protected $form;

    protected $division;
    /**
     * @var User
     */
    protected $user;

    public function testEmptyForm()
    {
        $this->form->validate();

        verify("name is required", $this->form->errors)->hasKey('name');
        verify("username is not required when no user permissions", $this->form->errors)->hasntKey('username');
        verify("color is required", $this->form->errors)->hasKey('color');
        verify("division_ids is required", $this->form->errors)->hasKey('division_ids');
    }

    public function testFilledForm()
    {
        $this->form->setAttributes([
            'name' => $this->tester->getFaker()->firstName,
            'division_ids' => [$this->division->id],
            'create_user' => false,
            'color' => $this->tester->getFaker()->colorName,
            'division_service_ids' => json_encode([])
        ]);

        $this->form->validate();

        verify("name is not required", $this->form->errors)->hasntKey('name');
        verify("username is not required with user permissions", $this->form->errors)->hasntKey('username');
        verify("color is not required", $this->form->errors)->hasntKey('color');
        verify("division_ids is not required", $this->form->errors)->hasntKey('division_ids');
    }

    public function testUsername()
    {
        $this->form->setAttributes([
            'create_user' => true
        ]);

        $this->form->validate();

        verify("username is required with user permissions", $this->form->errors)->hasKey('username');

        $this->form->setAttributes([
            'username' => $this->user->username,
            'division_service_ids' => json_encode([]),
            'create_user' => true
        ]);
        $this->form->validate();

        verify("username is not empty", $this->form->errors)->hasntKey('username');

        $existingStaff = $this->tester->getFactory()->create(Staff::class, [
            'user_id' => $this->user->id
        ]);

        $this->form->division_service_ids = json_encode([]);
        $this->form->validate();

        verify("username is already taken", $this->form->errors)->hasKey('username');
    }

    protected function _before()
    {
        $tariff = $this->tester->getFactory()->create(Tariff::class, ['staff_qty' => 10]);
        $company = $this->tester->getFactory()->create(Company::class, ['tariff_id' => $tariff->id]);
        $this->division = $this->tester->getFactory()->create(Division::class, ['company_id' => $company->id]);
        $this->user = $this->tester->getFactory()->create(User::class, [
            'company_id' => $company->id,
            'status'     => User::STATUS_ENABLED
        ]);

        $this->form = new StaffCreateForm($company->id);
    }

    // tests

    protected function _after()
    {
    }
}