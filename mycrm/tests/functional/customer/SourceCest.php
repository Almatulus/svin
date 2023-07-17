<?php

namespace tests\functional\customer;

use FunctionalTester;
use core\models\customer\CompanyCustomer;
use core\models\customer\Customer;
use core\models\customer\CustomerSource;

class SourceCest
{
    private $responseFormat
        = [
            'id'         => 'integer',
            'name'       => 'string',
            'company_id' => 'integer',
            'count'      => 'integer',
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
        $I->wantToTest('Customer source index');
        $I->sendGET('customer/source');
        $I->seeResponseCodeIs(401);

        $user = $I->login();

        $I->getFactory()->seed(5, CustomerSource::class, [
            'company_id' => $user->company_id,
        ]);
        $I->sendGET('customer/source');
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType($this->responseFormat, '$.[*]');
    }

    public function view(FunctionalTester $I)
    {
        $I->wantToTest('Customer source view');
        $I->sendGET('customer/1');
        $I->seeResponseCodeIs(401);

        $user = $I->login();

        $model = $I->getFactory()->create(CustomerSource::class, [
            'company_id' => $user->company_id,
        ]);

        $I->sendGET("customer/source/{$model->id}");
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType($this->responseFormat);
    }

    public function create(FunctionalTester $I)
    {
        $I->wantToTest('Customer source create');
        $I->checkLogin('customer/source');

        $user = $I->login();
        $name = $I->getFaker()->name;

        $I->sendPOST("customer/source");
        $I->seeResponseCodeIs(400);

        $I->sendPOST("customer/source", ['name' => $name]);
        $I->seeResponseCodeIs(201);
        $I->seeRecord(CustomerSource::class, [
            'name' => $name,
            'company_id' => $user->company_id
        ]);

        $I->seeResponseMatchesJsonType($this->responseFormat);
    }

    public function update(FunctionalTester $I)
    {
        $I->wantToTest('Customer source update');
        $I->checkLogin('customer/source');

        $user = $I->login();

        $customerSource = $I->getFactory()->create(CustomerSource::class, [
            'name' => $I->getFaker()->name
        ]);

        $I->sendPUT("customer/source/{$customerSource->id}");
        $I->seeResponseCodeIs(200);

        $name = $I->getFaker()->name;
        $I->sendPUT("customer/source/{$customerSource->id}", [
            'name' => $name
        ]);

        $I->seeResponseCodeIs(200);

        $I->seeRecord(CustomerSource::class, [
            'name' => $name
        ]);

        $I->seeResponseMatchesJsonType($this->responseFormat);
    }

    public function move(FunctionalTester $I)
    {
        $I->wantToTest('Customer source move');
        $I->checkLogin('customer/source');

        $user = $I->login();

        $customerSourceOld = $I->getFactory()->create(CustomerSource::class, [
            'name' => $I->getFaker()->name,
            'company_id' => $user->company_id
        ]);

        $customerSourceNew = $I->getFactory()->create(CustomerSource::class, [
            'name' => $I->getFaker()->name,
            'company_id' => $user->company_id
        ]);

        $I->getFactory()->create( CompanyCustomer::class, [
            'company_id' => $user->company_id,
            'source_id' => $customerSourceOld->id
        ]);

        $I->sendPUT("customer/source/{$customerSourceOld->id}/destination/$customerSourceNew->id");

        $I->seeResponseCodeIs(200);

        $I->seeRecord(CompanyCustomer::class, [
            'source_id' => $customerSourceNew->id
        ]);

        $I->seeResponseMatchesJsonType(['total_moved'=>'integer']);
        $I->seeResponseContains('1');
    }
}
