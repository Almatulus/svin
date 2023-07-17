<?php

namespace api\tests\company;

use FunctionalTester;
use core\models\CompanyPaymentLog;

class PaymentCest
{
    protected $responseFormat = [
        'id'             => 'integer',
        'value'          => 'integer',
        'currency'       => 'integer',
        'code'           => 'string',
        'created_time'   => 'string',
        'confirmed_time' => 'string',
        'description'    => 'string',
        'message'        => 'string',
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
        $I->wantToTest('Company payment index');

        $I->amGoingTo('Fetch payment logs with invalid credentials');
        $I->sendGET('company/payment');
        $I->seeResponseCodeIs(401);

        $user = $I->login();

        $I->getFactory()->seed(2, CompanyPaymentLog::class, ['company_id' => $user->company_id]);

        $I->amGoingTo('Fetch payment logs');
        $I->sendGET('company/payment');
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType($this->responseFormat, '$.[*]');
    }

    public function export(FunctionalTester $I)
    {
        $I->wantToTest('Company payment export');

        $I->amGoingTo('Export payment logs with invalid credentials');
        $I->sendGET('company/payment');
        $I->seeResponseCodeIs(401);

        $user = $I->login();

        $I->getFactory()->seed(2, CompanyPaymentLog::class, ['company_id' => $user->company_id]);

        $I->amGoingTo('Export payment logs');
        $I->sendGET('company/payment');
        $I->seeResponseCodeIs(200);
    }
}
