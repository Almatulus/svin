<?php

namespace api\tests\warehouse;

use core\models\company\Company;
use core\models\division\Division;
use core\models\warehouse\Category;
use core\models\warehouse\Product;
use FunctionalTester;

class ProductCest
{
    private $responseFormat
        = [
            "id"             => 'integer',
            'product_id'     => 'integer',
            "text"           => "string",
            "name"           => "string",
            "price"          => 'integer',
            "purchase_price" => 'integer',
            "vat"            => 'integer',
            "stock_level"    => 'integer',
            "quantity"       => 'integer',
            "unit"           => "array",
            "category"       => "array",
        ];

    public function _before(FunctionalTester $I)
    {
    }

    public function _after(FunctionalTester $I)
    {
    }

    public function index(FunctionalTester $I)
    {
        $I->wantToTest('Warehouse product index');

        $company = $I->getFactory()->create(Company::class);
        $division_1 = $I->getFactory()->create(Division::class, [
            'company_id' => $company->id,
        ]);
        $category = $I->getFactory()->create(Category::class);
        $I->getFactory()->create(Product::class, [
            'status'      => Product::STATUS_ENABLED,
            'division_id' => $division_1->id,
            'company_id'  => $company->id,
            'category_id' => $category->id
        ]);
        $division_2 = $I->getFactory()->create(Division::class, [
            'company_id' => $company->id,
        ]);

        $I->sendGET("product");
        $I->seeResponseCodeIs(401);

        $I->login();
        $I->sendGET("product", [
            'division_id' => $division_1->id,
            'expand'      => 'category',
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType($this->responseFormat, '$.[*]');

        $I->sendGET("product", [
            'division_id' => $division_2->id,
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseEquals(json_encode([]));
    }
}
