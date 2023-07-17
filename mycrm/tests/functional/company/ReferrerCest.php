<?php

namespace tests\functional\customer;

use core\models\company\Referrer;
use FunctionalTester;

class ReferrerCest
{
    private $responseFormat = [
        'id'          => 'integer',
        'name'        => 'string'
    ];

    public function _before(FunctionalTester $I)
    {
    }

    public function _after(FunctionalTester $I)
    {
    }

    // tests
    public function index(FunctionalTester $I)
    {
        $I->wantToTest('Company referrer index');
        $I->sendGET('company/referrer');
        $I->seeResponseCodeIs(401);

        $user = $I->login();

        $I->getFactory()->create(Referrer::class, [
            'company_id' => $user->company_id
        ]);

        $I->sendGET('company/referrer');
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType($this->responseFormat, '$.[*]');
    }

    public function view(FunctionalTester $I)
    {
        $I->wantToTest('Company referrer view');
        $I->sendGET('company/referrer/1');
        $I->seeResponseCodeIs(401);

        $user = $I->login();

        $referrer = $I->getFactory()->create(Referrer::class, [
            'company_id' => $user->company_id
        ]);

        $I->sendGET("company/referrer/{$referrer->id}");
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType($this->responseFormat);
    }

    public function create(FunctionalTester $I)
    {
        $I->wantToTest('Company referrer create');

        $I->sendPOST('company/referrer');
        $I->seeResponseCodeIs(401);

        $user = $I->login();

        $I->sendPOST("company/referrer", ['name' => 'check']);
        $I->seeResponseCodeIs(201);
    }
}
