<?php

namespace api\tests\statistic;

use FunctionalTester;
use core\models\division\Division;
use core\models\Staff;

class StaffCest
{
    private $responseFormat = [
        'id'                  => 'integer',
        'name'                => 'string',
        'surname'             => 'string|null',
        'revenue'             => 'integer',
        "position"            => "array|null",
        "ordersCount"         => "integer",
        "canceledOrdersCount" => "integer",
        'productsCount'       => 'integer',
        'servicesCount'       => 'integer',
        'workedHours'         => 'string',
        'revenueShare'        => 'string'
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
        $I->wantToTest("Statistic staff index");

        $I->sendGET('statistic/staff');
        $I->seeResponseCodeIs(401);

        $user = $I->login();

        $I->sendGET('statistic/staff');
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType([]);

        $division = $I->getFactory()->create(Division::class, ['company_id' => $user->company_id]);
        $staff = $I->getFactory()->seed(2, Staff::class);
        foreach ($staff as $employer) {
            $employer->link('divisions', $division);
        }

        $I->sendGET('statistic/staff');
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType($this->responseFormat, '$.[*]');
    }

    // tests
    public function top(FunctionalTester $I)
    {
        $I->wantToTest("Statistic staff top");

        $I->sendGET('statistic/staff/top');
        $I->seeResponseCodeIs(401);

        $user = $I->login();

        $I->sendGET('statistic/staff/top');
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType([
            'maxRevenue'    => 'null',
            'minWorkedTime' => 'null',
            'maxWorkedTime' => 'null',
        ]);

        $division = $I->getFactory()->create(Division::class, ['company_id' => $user->company_id]);
        $staff = $I->getFactory()->seed(2, Staff::class);
        foreach ($staff as $employer) {
            $employer->link('divisions', $division);
        }
        $I->sendGET('statistic/staff/top');
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType([
            'maxRevenue'    => $this->responseFormat,
            'minWorkedTime' => $this->responseFormat,
            'maxWorkedTime' => $this->responseFormat,
        ]);
    }
}
