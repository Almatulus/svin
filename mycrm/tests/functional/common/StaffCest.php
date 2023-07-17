<?php

namespace common;


use core\models\company\Company;
use core\models\division\Division;
use core\models\Staff;
use FunctionalTester;

class StaffCest
{
    private $_responseFormat = [
        'id'         => 'integer',
        'name'       => 'string',
        'surname'    => 'string|null',
        'birth_date' => 'string|null',
        'divisions'  => 'array'
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
        $employers = $I->getFactory()->seed(3, Staff::class);

        foreach ($employers as $employer) {
            $employer->link('divisions', $division);
        }

        $I->sendGET('public/staff?expand=divisions');
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
        $employers = $I->getFactory()->seed(3, Staff::class);

        foreach ($employers as $employer) {
            $employer->link('divisions', $division);
        }

        $I->sendGET('public/staff?expand=divisions');
        $I->seeResponseMatchesJsonType([], "$.[*]");
    }
}
