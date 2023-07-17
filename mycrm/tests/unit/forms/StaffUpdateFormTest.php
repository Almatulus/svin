<?php

namespace forms;

use core\forms\staff\StaffCreateForm;
use core\forms\staff\StaffUpdateForm;
use core\models\company\Company;
use core\models\company\Tariff;
use core\models\division\Division;
use core\models\Staff;
use core\models\user\User;

class StaffUpdateFormTest extends \Codeception\Test\Unit
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
     * @var Staff
     */
    protected $staff;
    /**
     * @var User
     */
    protected $user;
    protected $anotherUser;

    public function testEmptyForm()
    {
        $this->form->setAttributes([
            'name' => '',
            'color' => '',
            'create_user' => false
        ]);

        $this->form->validate();

        verify("name is required", $this->form->errors)->hasKey('name');
        verify("username is not required when no user permissions", $this->form->errors)->hasntKey('username');
        verify("color is required", $this->form->errors)->hasKey('color');
        verify("division_ids is required", $this->form->errors)->hasKey('division_ids');
    }

    public function testFilledForm()
    {
        $this->form->setAttributes([
            'division_ids' => [$this->division->id],
        ]);
        $this->form->validate();

        verify("name is not required", $this->form->errors)->hasntKey('name');
        verify("username is required with user permissions", $this->form->errors)->hasntKey('username');
        verify("color is not required", $this->form->errors)->hasntKey('color');
        verify("division_ids is not required", $this->form->errors)->hasntKey('division_ids');
    }

    public function testUsername()
    {
        $this->form->setAttributes([
            'username' => $this->anotherUser->username,
            'division_service_ids' => json_encode([]),
            'create_user' => true
        ]);
        $this->form->validate();

        verify("username is not empty", $this->form->errors)->hasntKey('username');

        $existingStaff = $this->tester->getFactory()->create(Staff::class, [
            'user_id' => $this->anotherUser->id
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

        $this->anotherUser =  $this->tester->getFactory()->create(User::class, [
            'company_id' => $company->id,
            'status'     => User::STATUS_ENABLED
        ]);

        $this->staff = $this->tester->getFactory()->create(Staff::class, [
            'user_id' => $this->user->id
        ]);

        $this->form = new StaffUpdateForm($this->staff);
    }

    // tests

    protected function _after()
    {
    }
}