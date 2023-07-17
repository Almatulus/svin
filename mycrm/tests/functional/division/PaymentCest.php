<?php

namespace division;


use core\models\division\Division;
use core\models\division\DivisionPayment;
use FunctionalTester;

class PaymentCest
{
    private $responseFormat = [
        'id'   => 'integer',
        'name' => 'string',
        'type' => 'integer|null'
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
        $I->wantToTest('Division payment index');

        $I->sendGET('division/payment');
        $I->seeResponseCodeIs(401);

        $user = $I->login();

        $notOwnDivision = $I->getFactory()->create(Division::class);
        $division = $I->getFactory()->create(Division::class, ['company_id' => $user->company_id]);

        $I->amGoingTo("Fetch payments of another company's division");
        $I->getFactory()->create(DivisionPayment::class, ['division_id' => $notOwnDivision->id]);
        $I->sendGET("division/{$notOwnDivision->id}/payment");
        $I->seeResponseCodeIs(200);
        $I->seeResponseEquals('[]');

        $I->amGoingTo("Fetch empty list of division's payments");
        $I->sendGET("division/{$division->id}/payment");
        $I->seeResponseCodeIs(200);
        $I->seeResponseEquals('[]');

        $I->amGoingTo('Fetch payments of division');
        $I->getFactory()->create(DivisionPayment::class, ['division_id' => $division->id]);
        $I->sendGET("division/{$division->id}/payment");
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType($this->responseFormat, '$.[*]');
    }
}
