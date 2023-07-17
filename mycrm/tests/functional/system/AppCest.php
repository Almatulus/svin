<?php

namespace tests\functional\system;

use FunctionalTester;

class AppCest
{
    private $responseFormat
        = [
            'name'       => 'string',
            'version'    => 'string',
            'update_url' => 'string',
        ];

    // tests
    public function ios(FunctionalTester $I)
    {
        $I->wantToTest('iOS Application info');
        $I->sendGET('app/ios');
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType($this->responseFormat);
    }

    public function android(FunctionalTester $I)
    {
        $I->wantToTest('Android Application info');
        $I->sendGET('app/android');
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType($this->responseFormat);
    }

    public function options(FunctionalTester $I)
    {
        $I->wantToTest('Application options');
        $I->sendOPTIONS('app/ios');
        $I->seeResponseCodeIs(200);
    }
}
