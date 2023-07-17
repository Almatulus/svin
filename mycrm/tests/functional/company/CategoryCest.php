<?php

namespace tests\company;

use core\models\division\Division;
use core\models\ServiceCategory;
use FunctionalTester;

class CategoryCest
{
    private $responseFormat = [
        'id'                 => 'integer',
        'name'               => 'string',
        'division_count'     => 'integer',
        'parent_category_id' => 'integer|null'
    ];

    public function _before(FunctionalTester $I)
    {
    }

    public function _after(FunctionalTester $I)
    {
    }

    public function index(FunctionalTester $I)
    {
        $I->sendGET('service/category');
        $I->seeResponseCodeIs(401);

        $user = $I->login();

        $rootCategory = $I->getFactory()->create(ServiceCategory::class, [
            'type' => ServiceCategory::TYPE_CATEGORY_STATIC
        ]);
        $I->getFactory()->create(Division::class, [
            'category_id' => $rootCategory->id,
            'company_id'  => $user->company_id
        ]);
        $I->getFactory()->create(ServiceCategory::class, [
            'company_id'         => $user->company_id,
            'type'               => ServiceCategory::TYPE_CATEGORY_DYNAMIC,
            'parent_category_id' => $rootCategory->id
        ]);
        $I->sendGET('service/category');
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType($this->responseFormat, '$.[*]');
    }

    public function view(FunctionalTester $I)
    {
        $I->sendGET('service/category/1');
        $I->seeResponseCodeIs(401);

        $user = $I->login();

        $category = $I->getFactory()->create(ServiceCategory::class);
        $I->sendGET("service/category/{$category->id}");
        $I->seeResponseCodeIs(403);

        $category = $I->getFactory()->create(ServiceCategory::class, ['company_id' => $user->company_id]);
        $I->sendGET("service/category/{$category->id}");
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType($this->responseFormat);
    }

    public function create(FunctionalTester $I)
    {
        $I->sendPOST('service/category');
        $I->seeResponseCodeIs(401);

        $user = $I->login();

        $I->sendPOST('service/category', ['name' => '']);
        $I->seeResponseCodeIs(403);

        $I->assignPermission($user, 'serviceCategoryCreate');

        $I->sendPOST('service/category', ['name' => '']);
        $I->seeResponseCodeIs(422);

        $rootCategory = $I->getFactory()->create(ServiceCategory::class, [
            'type' => ServiceCategory::TYPE_CATEGORY_STATIC
        ]);
        $I->sendPOST('service/category', [
            'name'               => $I->getFaker()->name,
            'parent_category_id' => $rootCategory->id
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType($this->responseFormat);
    }

    public function update(FunctionalTester $I)
    {
        $I->sendPUT('service/category/1');
        $I->seeResponseCodeIs(401);

        $user = $I->login();

        $category = $I->getFactory()->create(ServiceCategory::class);
        $I->sendPUT("service/category/{$category->id}");
        $I->seeResponseCodeIs(403);

        $category = $I->getFactory()->create(ServiceCategory::class, [
            'type' => ServiceCategory::TYPE_CATEGORY_STATIC
        ]);
        $I->sendPUT("service/category/{$category->id}");
        $I->seeResponseCodeIs(403);

        $rootCategory = $I->getFactory()->create(ServiceCategory::class, [
            'type' => ServiceCategory::TYPE_CATEGORY_STATIC
        ]);
        $category = $I->getFactory()->create(ServiceCategory::class, [
            'company_id'         => $user->company_id,
            'type'               => ServiceCategory::TYPE_CATEGORY_DYNAMIC,
            'parent_category_id' => $rootCategory->id
        ]);
        $I->sendPUT("service/category/{$category->id}", ['name' => ""]);
        $I->seeResponseCodeIs(422);

        $I->sendPUT("service/category/{$category->id}", [
            'name'               => $I->getFaker()->name,
            'parent_category_id' => $rootCategory->id
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType($this->responseFormat);
    }

    public function delete(FunctionalTester $I)
    {
        $I->sendDELETE('service/category/1');
        $I->seeResponseCodeIs(401);

        $user = $I->login();

        $category = $I->getFactory()->create(ServiceCategory::class);
        $I->sendDELETE("service/category/{$category->id}");
        $I->seeResponseCodeIs(403);

        $category = $I->getFactory()->create(ServiceCategory::class, [
            'type' => ServiceCategory::TYPE_CATEGORY_STATIC
        ]);
        $I->sendDELETE("service/category/{$category->id}");
        $I->seeResponseCodeIs(403);

        $rootCategory = $I->getFactory()->create(ServiceCategory::class, [
            'type' => ServiceCategory::TYPE_CATEGORY_STATIC
        ]);
        $category = $I->getFactory()->create(ServiceCategory::class, [
            'company_id'         => $user->company_id,
            'type'               => ServiceCategory::TYPE_CATEGORY_DYNAMIC,
            'parent_category_id' => $rootCategory->id
        ]);
        $I->sendDELETE("service/category/{$category->id}");
        $I->seeResponseCodeIs(204);
    }
}
