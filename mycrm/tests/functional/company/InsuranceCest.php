<?php

namespace tests\functional\customer;

use core\models\company\Insurance;
use core\models\InsuranceCompany;
use FunctionalTester;

class InsuranceCest
{
    private $responseFormat = [
        'id'         => 'integer',
        'name'       => 'string',
        'is_enabled' => 'boolean'
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
        $I->wantToTest('Company insurance index');
        $I->sendGET('insurance-company');
        $I->seeResponseCodeIs(401);

        $user = $I->login();

        $I->getFactory()->create(Insurance::class, [
            'company_id' => $user->company_id
        ]);

        $I->sendGET('insurance-company');
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType($this->responseFormat, '$.[*]');
    }

    public function update(FunctionalTester $I)
    {
        $I->wantToTest('Company insurance update');
        $I->sendPUT('insurance-company');
        $I->seeResponseCodeIs(401);

        $user = $I->login();

        $I->sendPUT('insurance-company', [
            "companies" => [0, "asd"]
        ]);
        $I->seeResponseCodeIs(422);

        $insuranceCompanies = $I->getFactory()->seed(3, InsuranceCompany::class);
        $data = array_map(function (InsuranceCompany $company) {
            return $company->id;
        }, $insuranceCompanies);
        $I->sendPUT('insurance-company', [
            "companies" => $data
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson($data);
    }
}
