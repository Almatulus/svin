<?php

namespace tests\division;

use core\models\division\Division;
use FunctionalTester;

class DefaultCest
{
    private $responseFormat = [
        'address' => 'string',
        'category_id' => 'integer',
        'city_id' => 'integer',
        'city_name' => 'string',
        'country_id'                => 'integer',
        'country_name'              => 'string',
        'company_id'                => 'integer',
        'default_notification_time' => 'integer',
        'description'               => 'string|null',
        'id'                        => 'integer|null',
        'key'                       => 'string',
        'latitude'                  => 'float|integer',
        'longitude'                 => 'float|integer',
        'name'                      => 'string',
        'phone'                     => 'string',
        'rating'                    => 'integer|float',
        'status'                    => 'integer',
        'status_name'               => 'string',
        'status_list'               => 'array',
        'url'                       => 'string|null',
        'working_finish'            => 'string',
        'working_start'             => 'string',
        'logo_path'                 => 'string|null'
    ];

    public function _before(FunctionalTester $I)
    {
    }

    public function _after(FunctionalTester $I)
    {
    }

    public function index(FunctionalTester $I)
    {
        $I->getFactory()->seed(5, Division::class, []);

        $I->sendGET('division');
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType($this->responseFormat, '$.[*]');
    }
}
