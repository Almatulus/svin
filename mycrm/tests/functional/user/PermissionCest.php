<?php

namespace api\tests\user;

use core\models\user\User;
use FunctionalTester;

class PermissionCest
{
    private $responseFormat = [

    ];

    public function _before(FunctionalTester $I)
    {
    }

    public function _after(FunctionalTester $I)
    {
    }

    public function index(FunctionalTester $I)
    {
        $I->sendGET('user/permission');
        $I->seeResponseCodeIs(401);

        $user = $I->getFactory()->create(User::class, []);
        $I->assignPermission($user, 'timetableView');
        $I->assignPermission($user, 'companyCustomerOwner');
        $I->assignPermission($user, 'orderOwner');
        $I->assignPermission($user, 'staffReviewAdmin');
        $I->assignPermission($user, 'divisionReviewAdmin');
        $I->assignPermission($user, 'companyView');
        $I->assignPermission($user, 'divisionServiceView');
        $I->assignPermission($user, 'divisionServiceCreate');
        $I->assignPermission($user, 'divisionServiceUpdate');

        $I->login($user);

        $I->sendGET('user/permission');
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson([
            'timetable' => ['timetable'],
            'customers' => ['customer'],
            'orders'    => ['order', 'staffReview', 'divisionReview'],
            'services'  => ['divisionService', 'divisionServiceCreate', 'divisionServiceUpdate'],
        ]);

        $I->dontSeeResponseContainsJson([
            'settings',
            'finance',
            'warehouse',
            'services',
            'statistic'
        ]);
    }
}
