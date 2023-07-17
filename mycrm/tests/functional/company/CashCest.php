<?php

namespace tests\functional\customer;

use core\models\division\Division;
use core\models\finance\CompanyCash;
use core\models\Staff;
use core\models\user\User;
use core\models\user\UserDivision;
use FunctionalTester;

class CashCest
{
    private $responseFormat = [
        'id'          => 'integer',
        'name'        => 'string',
        'type'        => 'integer',
        'init_money'  => 'integer',
        'comments'    => 'string|null',
        'is_deletable'=> 'boolean',
        'division_id' => 'integer',
        'status'      => 'integer'
    ];

    private $user;

    private $division;

    public function _before(FunctionalTester $I)
    {
        $this->user = $I->getFactory()->create(User::class);
        $this->division = $I->getFactory()->create(Division::class, [
            'company_id' => $this->user->company_id,
        ]);
    }

    public function _after(FunctionalTester $I)
    {
    }

    public function index(FunctionalTester $I)
    {
        $I->wantToTest('Company cash index');
        $I->sendGET('company/cash');
        $I->seeResponseCodeIs(401);

        $user = $I->login();

        //  Check not permitted
        $notPermitted = $I->getFactory()->seed(3, CompanyCash::class, [
            'company_id' => $user->company_id
        ]);

        $I->sendGET('company/cash');
        $I->seeResponseCodeIs(200);
        $I->seeResponseEquals('[]');

        //  Check permitted
        $staff = $I->getFactory()->create(Staff::class, [
            'user_id' => $user->id
        ]);

        $userDivision = $I->getFactory()->create(UserDivision::class, [
            'staff_id' => $staff->id,
            'division_id' => $this->division->id
        ]);

        $permitted = $I->getFactory()->seed(3, CompanyCash::class, [
            'company_id' => $user->company_id,
            'division_id' => $this->division->id
        ]);

        $I->sendGET('company/cash');
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType($this->responseFormat, '$.[*]');
    }

    public function view(FunctionalTester $I)
    {
        $I->wantToTest('Company cash view');
        $I->sendGET('company/cash/1');
        $I->seeResponseCodeIs(401);

        $user = $I->login();

        $cash = $I->getFactory()->create(CompanyCash::class, [
            'company_id' => $user->company_id
        ]);

        $I->sendGET("company/cash/{$cash->id}");
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType($this->responseFormat);
    }
}
