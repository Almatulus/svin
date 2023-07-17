<?php

namespace api\tests\country;

use core\models\Country;
use FunctionalTester;

class DefaultCest
{
    private $responseFormat = [
        'id' => 'integer',
        'name' => 'string',
        'active' => 'boolean',
    ];

    public function _before(FunctionalTester $I)
    {
    }

    public function _after(FunctionalTester $I)
    {
    }

    public function index(FunctionalTester $I)
    {
        $I->wantToTest('Country index');

        $activeCountries = $I->getFactory()->seed(3, Country::class, [
            'active' => true,
        ]);

        $disabledCountries = $I->getFactory()->seed(3, Country::class, [
            'active' => false,
        ]);

        $I->sendGET('country');
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType($this->responseFormat, '$.[*]');

        // I shouldn't see disabled Countries

        $I->sendGET('country' . '?' . 'active=1');
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType($this->responseFormat, '$.[*]');

        foreach ($activeCountries as $country) {
            $I->seeResponseContainsJson(['id' => $country->id]);
        }
        foreach ($disabledCountries as $country) {
            $I->dontSeeResponseContainsJson(['id' => $country->id]);
        }
    }
}
