<?php

namespace api\tests\staff;

use core\models\StaffDivisionMap;
use FunctionalTester;
use core\models\division\Division;
use core\models\Staff;
use core\models\user\User;

class DefaultCest
{
    private $responseFormat
        = [
            'id'                    => 'integer',
            'name'                  => 'string',
            'surname'               => 'string',
            'phone'                 => 'string|null',
            'description'           => 'string|null',
            'description_private'   => 'string|null',
            'gender'                => 'integer',
            'gender_name'           => 'string',
            'has_calendar'          => 'integer',
            'birth_date'            => 'string|null',
            'color'                 => 'string',
            'see_own_orders'        => 'boolean',
            'can_create_order'      => 'boolean',
            'rating'                => 'string',
            'image'                 => 'string',
            'has_user_permissions'  => 'boolean',
            '_links'                => 'array'
        ];

    public function _after(FunctionalTester $I)
    {
    }

    public function index(FunctionalTester $I)
    {

        $staff = $I->getFactory()->create(Staff::class, []);

        $I->sendGET('staff?expand=rating');
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType($this->responseFormat, '$.[*]');
    }

    public function view(FunctionalTester $I){
        $staff = $I->getFactory()->create(Staff::class, []);

        $I->sendGET("staff/{$staff->id}?expand=rating");
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType($this->responseFormat);
    }
}
