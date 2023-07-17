<?php

namespace tests\functional\customer;

use FunctionalTester;
use core\models\customer\CustomerLoyalty;

class LoyaltyCest
{
    private $responseFormat = [
        'id' => 'integer',
        'customer_loyalty_id' => 'integer',
        'event' => 'integer',
        'amount' => 'integer',
        'discount' => 'integer',
        'rank' => 'integer|null',
        'category_id' => 'integer|null',
        'customer_category_id' => 'integer|null',
        'mode' => 'integer|null',
        'company_id' => 'integer',
        'trigger_title' => 'string',
        'event_title' => 'string'
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
        $I->wantToTest('Customer Loyalty index');
        $I->sendGET('customer/loyalty');
        $I->seeResponseCodeIs(401);

        $user = $I->login();

        $I->getFactory()->create(CustomerLoyalty::class, [
            'company_id' => $user->company_id,
        ]);

        $I->sendGET('customer/loyalty');
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType($this->responseFormat, '$.[*]');
    }

    public function view(FunctionalTester $I)
    {
        $I->wantToTest('Customer Loyalty view');
        $I->sendGET('customer/loyalty/1');
        $I->seeResponseCodeIs(401);

        $user = $I->login();

        $model = $I->getFactory()->create(CustomerLoyalty::class, [
            'company_id'  => $user->company_id
        ]);

        $I->sendGET("customer/loyalty/{$model->id}");
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType($this->responseFormat);
    }

    public function create(FunctionalTester $I)
    {
        $I->wantToTest('Customer Loyalty create');
        $I->checkLogin('customer/loyalty');

        $user = $I->login();

        $I->sendPOST("customer/loyalty");
        $I->seeResponseCodeIs(400);

        $I->sendPOST("customer/loyalty", [
            'company_id' => $user->company_id,
            'discount' => 50,
            'mode' => CustomerLoyalty::MODE_ADD_DISCOUNT,
            'amount' => 50000,
            'event' => CustomerLoyalty::EVENT_MONEY
        ]);

        $I->seeResponseCodeIs(200);

        $I->seeRecord(CustomerLoyalty::class, [
            'company_id' => $user->company_id
        ]);

        $I->seeResponseMatchesJsonType($this->responseFormat);
    }

    public function update(FunctionalTester $I)
    {
        $I->wantToTest('Customer Loyalty update');
        $I->checkLogin('customer/loyalty');

        $user = $I->login();

        $customerLoyalty= $I->getFactory()->create(CustomerLoyalty::class, [
            'company_id' => $user->company_id
        ]);

        $I->sendPUT("customer/loyalty/{$customerLoyalty->id}");
        $I->seeResponseCodeIs(200);

        $I->sendPUT("customer/loyalty/{$customerLoyalty->id}", [
            'discount' => 77
        ]);

        $I->seeResponseCodeIs(200);

        $I->seeRecord(CustomerLoyalty::class, [
            'discount' => 77
        ]);
    }
}
