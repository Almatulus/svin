<?php

namespace models;

use core\models\company\Company;
use core\models\company\Tariff;
use core\models\division\Division;
use core\models\Staff;
use core\models\user\User;

class StaffTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    protected $division;

    /**
     * @var Staff
     */
    protected $staff;
    /**
     * @var User
     */
    protected $user;

    public function testChangePhone()
    {
        $this->expectException(\DomainException::class);
        $this->staff->changePhone("some wrong format");
    }

    public function testHasUserPermissions()
    {
        verify("staff has permissions", $this->staff->hasUserPermissions())->equals(true);
        $this->staff->user_id = null;
        verify("staff doesn't have permissions", $this->staff->hasUserPermissions())->equals(false);
    }

    public function testRemoveUserPermissions()
    {
        $user = $this->staff->user;
        $this->staff->removeUserPermissions();
        verify("staff doesn't have permissions", $this->staff->hasUserPermissions())->equals(false);
        verify("staff user is disabled", $user->status)->equals(User::STATUS_DISABLED);
    }

    public function testGrantSystemAccess()
    {
        $this->staff->grantSystemAccess(
            $this->user,
            true,
            true,
            true
        );
        verify("can see own orders", $this->staff->see_own_orders)->equals(true);
        verify("can see create order", $this->staff->create_order)->equals(true);
        verify("can see create order", $this->staff->see_customer_phones)->equals(true);
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

        $this->staff = $this->tester->getFactory()->create(Staff::class, [
            'user_id' => $this->user->id
        ]);
    }

    // tests

    protected function _after()
    {
    }
}