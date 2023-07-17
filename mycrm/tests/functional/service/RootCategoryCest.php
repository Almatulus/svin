<?php

namespace api\tests\schedule;

use core\models\ServiceCategory;
use FunctionalTester;

class RootCategoryCest
{
    private $responseFormat
        = [
            'id'                    => 'integer',
            'name'                  => 'string',
            'division_count'        => 'integer',
            'parent_category_id'    => 'integer'
        ];

    public function _after(FunctionalTester $I)
    {
    }

    public function index(FunctionalTester $I)
    {
        $I->getFactory()->seed(5,ServiceCategory::class, [
            'parent_category_id' => null
        ]);

        $I->sendGET('service/root-category');
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType($this->responseFormat, '$.[*]');
    }

    public function view(FunctionalTester $I){

        $category = $I->getFactory()->create(ServiceCategory::class, [
            'parent_category_id' => null
        ]);

        $I->sendGET("service/root-category/{$category->id}");
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType($this->responseFormat);
    }
}
