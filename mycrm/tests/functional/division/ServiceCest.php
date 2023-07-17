<?php

namespace tests\division;

use core\models\division\Division;
use core\models\division\DivisionService;
use core\models\Staff;
use FunctionalTester;

class ServiceCest
{
    private $responseFormat = [
        'id'                   => 'integer',
        'price'                => 'integer',
        'price_max'            => 'integer|null',
        'average_time'         => 'integer',
        'service_name'         => 'string',
        'description'          => 'string|null',
        'status'               => 'integer',
        'publish'              => 'boolean',
        'insurance_company_id' => 'integer|null',
        'is_trial'             => 'boolean',
        'notification_delay'   => 'integer|null',
        'divisions'            => 'array'
    ];

    public function _before(FunctionalTester $I)
    {
    }

    public function _after(FunctionalTester $I)
    {
    }

    public function index(FunctionalTester $I)
    {
        $I->sendGET('division/service');
        $I->seeResponseCodeIs(401);

        $user = $I->login();
        $division = $I->getFactory()->create(Division::class, ['company_id' => $user->company_id]);
        $services = $I->getFactory()->seed(2, DivisionService::class);
        foreach ($services as $service) {
            $service->link('divisions', $division);
        }

        $I->sendGET('division/service?expand=divisions');
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType($this->responseFormat, "$.[*]");
        $I->seeHttpHeader("X-Pagination-Total-Count", sizeof($services));

        $staff = $I->getFactory()->create(Staff::class);
        $staff->link('divisionServices', $services[0]);
        $I->sendGET("division/{$division->id}/staff/{$staff->id}/service?expand=divisions");
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType($this->responseFormat, "$.[*]");
        $I->seeHttpHeader("X-Pagination-Total-Count", 1);
    }
}
