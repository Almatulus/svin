<?php

namespace common;


use core\models\company\Company;
use core\models\division\Division;
use core\models\division\DivisionService;
use core\models\Staff;
use FunctionalTester;

class ServiceCest
{
    private $_responseFormat = [
        'id'       => 'integer',
        'name'     => 'string',
        'duration' => 'integer',
        'price'    => 'integer'
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
        $company = $I->getFactory()->create(Company::class, [
            'enable_integration' => true
        ]);
        $division = $I->getFactory()->create(Division::class, [
            'company_id' => $company->id
        ]);
        $staff = $I->getFactory()->create(Staff::class);
        $staff->link('divisions', $division);
        $services = $I->getFactory()->seed(3, DivisionService::class);

        foreach ($services as $service) {
            $staff->link('divisionServices', $service);
        }

        $I->sendGET("public/staff/{$staff->id}/service");
        $I->seeResponseMatchesJsonType($this->_responseFormat, "$.[*]");
    }

    // tests
    public function indexError(FunctionalTester $I)
    {
        $company = $I->getFactory()->create(Company::class, [
            'enable_integration' => false
        ]);
        $division = $I->getFactory()->create(Division::class, [
            'company_id' => $company->id
        ]);
        $staff = $I->getFactory()->create(Staff::class);
        $staff->link('divisions', $division);
        $services = $I->getFactory()->seed(3, DivisionService::class);

        foreach ($services as $service) {
            $staff->link('divisionServices', $service);
        }

        $I->sendGET("public/staff/{$staff->id}/service");
        $I->seeResponseMatchesJsonType([], "$.[*]");
    }
}
