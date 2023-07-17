<?php

namespace staff;


use core\models\division\Division;
use core\models\division\DivisionService;
use core\models\ServiceCategory;
use core\models\Staff;
use FunctionalTester;

class CategoryCest
{
    private $responseFormat = [
        'id'                 => 'integer',
        'name'               => 'string',
        'parent_category_id' => 'integer|null',
        'services'           => 'array'
    ];

    public function _before(FunctionalTester $I)
    {
    }

    public function _after(FunctionalTester $I)
    {
    }

    /**
     * @group category
     */
    public function index(FunctionalTester $I)
    {
        $I->sendGET('division/1/staff/1/categories?expand=services');
        $I->seeResponseCodeIs(401);

        $user = $I->login();

        $I->sendGET('division/1/staff/0/categories?expand=services');
        $I->seeResponseCodeIs(404);

        $anotherStaff = $I->getFactory()->create(Staff::class);
        $I->sendGET("division/1/staff/{$anotherStaff->id}/categories?expand=services");
        $I->seeResponseCodeIs(403);

        $staff = $I->getFactory()->create(Staff::class, ['user_id' => $user->id]);
        $division = $I->getFactory()->create(Division::class, ['company_id' => $user->company_id]);
        $staff->link('divisions', $division);

        $I->sendGET("division/{$division->id}/staff/{$staff->id}/categories?expand=services");
        $I->seeResponseCodeIs(200);
        $I->seeResponseEquals(json_encode([]));

        $categories = $I->getFactory()->seed(2, ServiceCategory::class, ['company_id' => $user->company_id]);
        foreach ($categories as $category) {
            $services = $I->getFactory()->seed(2, DivisionService::class);
            foreach ($services as $service) {
                $service->link('categories', $category);
                $service->link('divisions', $division);
                $service->link('staffs', $staff);
            }
        }

        $I->sendGET("division/{$division->id}/staff/{$staff->id}/categories?expand=services");
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType($this->responseFormat, '$.[*]');
    }
}
