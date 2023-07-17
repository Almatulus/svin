<?php

namespace api\tests\statistic;

use FunctionalTester;

class DefaultCest
{
    public function _before(FunctionalTester $I)
    {
    }

    public function _after(FunctionalTester $I)
    {
    }

    public function index(FunctionalTester $I)
    {
        $I->wantToTest("Statistic index");

        $I->sendGET('statistic');
        $I->seeResponseCodeIs(401);

        $I->login();

        $I->sendGET('statistic');
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType([
            'income'         => 'integer',
            'profit'         => 'integer',
            'averageRevenue' => 'integer',
            'occupancy'      => 'string',
            'totalCount'     => 'integer',
            'disabledCount'  => 'integer',
            'finishedCount'  => 'integer',
            'enabledCount'   => 'integer',
            'revenues'       => 'array',
            'sources'        => 'array',
            'creators'       => 'array',
            'types'          => 'array',
        ]);
    }
}
