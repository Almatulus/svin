<?php

namespace api\tests\country;

use core\models\City;
use core\models\Country;
use FunctionalTester;

class CityCest
{
    private $responseFormat = [
        'id' => 'integer',
        'name' => 'string',
        'country_id' => 'integer',
        'country_name' => 'string',
    ];

    public function _before(FunctionalTester $I)
    {
    }

    public function _after(FunctionalTester $I)
    {
    }

    public function index(FunctionalTester $I)
    {
        $I->wantToTest('City index');

        $country1 = $I->getFactory()->create(Country::class, []);
        $country2 = $I->getFactory()->create(Country::class, []);

        $cities1 = $I->getFactory()->seed(2, City::class, [
            'country_id' => $country1->id,
        ]);

        $cities2 = $I->getFactory()->seed(2, City::class, [
            'country_id' => $country2->id,
        ]);

        $I->sendGET('country/city');
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType($this->responseFormat, '$.[*]');

        // I shouldn't see another Country's Cities

        $I->sendGET('country/city' . '?' . 'country_id=' . $country1->id);
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType($this->responseFormat, '$.[*]');

        foreach ($cities1 as $city) {
            $I->seeResponseContainsJson(['id' => $city->id]);
        }
        foreach ($cities2 as $city) {
            $I->dontSeeResponseContainsJson(['id' => $city->id]);
        }
    }
}
