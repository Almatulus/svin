<?php

namespace tests\functional\customer;

use FunctionalTester;
use core\models\customer\CompanyCustomer;
use core\models\customer\Customer;
use core\models\customer\CustomerCategory;

class CategoryCest
{
    private $responseFormat = [
        'id' => 'integer',
        'name' => 'string',
        'color' => 'string|null',
        'company_id' => 'integer',
        'discount' => 'integer|null',
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
        $I->wantToTest('Customer Category index');
        $I->sendGET('customer/category');
        $I->seeResponseCodeIs(401);

        $user = $I->login();

        $I->getFactory()->create(CustomerCategory::class, [
            'company_id' => $user->company_id
        ]);

        $I->sendGET('customer/category');
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType($this->responseFormat, '$.[*]');
    }

    public function view(FunctionalTester $I)
    {
        $I->wantToTest('Customer Category view');
        $I->sendGET('customer/category/1');
        $I->seeResponseCodeIs(401);

        $user = $I->login();

        $model = $I->getFactory()->create(CustomerCategory::class, [
            'company_id'  => $user->company_id
        ]);

        $I->sendGET("customer/category/{$model->id}");
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType($this->responseFormat);
    }

    public function create(FunctionalTester $I)
    {
        $I->wantToTest('Customer category create');
        $I->checkLogin('customer/category');

        $user = $I->login();

        $I->sendPOST("customer/category");
        $I->seeResponseCodeIs(400);

        $name = $I->getFaker()->name;
        $I->sendPOST("customer/category", [
            'name' => $name
        ]);

        $I->seeResponseCodeIs(200);

        $I->seeRecord(CustomerCategory::class, [
            'name' => $name,
            'company_id' => $user->company_id
        ]);

        $I->seeResponseMatchesJsonType($this->responseFormat);
    }

    public function update(FunctionalTester $I)
    {
        $I->wantToTest('Customer category update');
        $I->checkLogin('customer/category');

        $user = $I->login();

        $customerCategory = $I->getFactory()->create(CustomerCategory::class, [
            'name' => $I->getFaker()->name,
            'company_id' => $user->company_id
        ]);

        $I->sendPUT("customer/category/{$customerCategory->id}");
        $I->seeResponseCodeIs(200);

        $name = $I->getFaker()->name;
        $I->sendPUT("customer/category/{$customerCategory->id}", [
            'name' => $name
        ]);

        $I->seeResponseCodeIs(200);

        $I->seeRecord(CustomerCategory::class, [
            'name' => $name,
            'company_id' => $user->company_id
        ]);

        $I->seeResponseMatchesJsonType($this->responseFormat);
    }
}
