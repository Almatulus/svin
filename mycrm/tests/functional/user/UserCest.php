<?php

namespace api\tests\user;

use FunctionalTester;

class UserCest
{
    private $responseFormat
        = [
            'id'                   => 'integer',
            'username'             => 'string',
            'company_id'           => 'integer',
            'google_refresh_token' => 'string|null',
            'status'               => 'integer',
        ];

    public function index(FunctionalTester $I)
    {
        $I->sendGET('user');
        $I->seeResponseCodeIs(401);

        $I->login();

        $I->sendGET("user");
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType($this->responseFormat);
    }
}
