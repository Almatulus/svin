<?php

namespace tests\warehouse;

use core\models\warehouse\Category;
use core\models\warehouse\Product;
use FunctionalTester;

class CategoryCest
{
    private $responseFormat = [
        "id"         => "integer",
        "company_id" => "integer",
        "name"       => "string",
        "parent_id"  => "integer|null",
    ];

    public function _before(FunctionalTester $I)
    {
    }

    public function _after(FunctionalTester $I)
    {
    }

    public function index(FunctionalTester $I)
    {
        $I->sendGET('product/category');
        $I->seeResponseCodeIs(401);

        $user = $I->login();

        $categories = $I->getFactory()->seed(2, Category::class, ['company_id' => $user->company_id]);

        $I->sendGET("product/category?name={$categories[0]->name}");
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType($this->responseFormat, "$.[*]");

        $I->sendGET('product/category');
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType($this->responseFormat, "$.[*]");
    }

    public function view(FunctionalTester $I)
    {
        $I->sendGET("product/category/1");
        $I->seeResponseCodeIs(401);

        $user = $I->login();

        $category = $I->getFactory()->create(Category::class);
        $I->sendGET("product/category/{$category->id}");
        $I->seeResponseCodeIs(403);

        $category = $I->getFactory()->create(Category::class, ['company_id' => $user->company_id]);
        $I->sendGET("product/category/{$category->id}");
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType($this->responseFormat);
    }

    public function create(FunctionalTester $I)
    {
        $I->sendPOST('product/category');
        $I->seeResponseCodeIs(401);

        $user = $I->login();

        $I->sendPOST('product/category');
        $I->seeResponseCodeIs(422);

        $I->sendPOST("product/category", [
            'name' => $I->getFaker()->name
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType($this->responseFormat);
    }

    public function update(FunctionalTester $I)
    {
        $I->sendPUT('product/category/1');
        $I->seeResponseCodeIs(401);

        $user = $I->login();

        $category = $I->getFactory()->create(Category::class);
        $I->sendPUT("product/category/{$category->id}");
        $I->seeResponseCodeIs(403);

        $category = $I->getFactory()->create(Category::class, ['company_id' => $user->company_id]);
        $I->sendPUT("product/category/{$category->id}", ["name" => ""]);
        $I->seeResponseCodeIs(422);

        $I->sendPUT("product/category/{$category->id}", ["name" => $I->getFaker()->name]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType($this->responseFormat);
    }

    public function delete(FunctionalTester $I)
    {
        $I->sendDELETE('product/category/1');
        $I->seeResponseCodeIs(401);

        $user = $I->login();

        $category = $I->getFactory()->create(Category::class);
        $I->sendDELETE("product/category/{$category->id}");
        $I->seeResponseCodeIs(403);

        $category = $I->getFactory()->create(Category::class, ['company_id' => $user->company_id]);
        $I->getFactory()->create(Product::class, ['category_id' => $category->id]);
        $I->sendDELETE("product/category/{$category->id}");
        $I->seeResponseCodeIs(204);

        $category = $I->getFactory()->create(Category::class, ['company_id' => $user->company_id]);
        $I->sendDELETE("product/category/{$category->id}");
        $I->seeResponseCodeIs(204);
    }

}
