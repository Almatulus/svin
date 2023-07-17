<?php

namespace api\tests\newsLog;

use FunctionalTester;
use core\models\NewsLog;


class DefaultCest
{
    private $responseFormat
        = [
            'id' => 'integer',
            'link' => 'string',
            'text' => 'string',
        ];

    public function _before(FunctionalTester $I)
    {
    }

    public function _after(FunctionalTester $I)
    {
    }

    // tests
    public function index(FunctionalTester $I)
    {
        $I->wantToTest('NewsLog index');

        // Populate DB with two enabled and disabled NewsLogs
        $enabledLogs = $I->getFactory()->seed(2, NewsLog::class, [
            'status' => NewsLog::STATUS_ENABLED,
        ]);

        $disabledLogs = $I->getFactory()->seed(2, NewsLog::class, [
            'status' => NewsLog::STATUS_DISABLED,
        ]);

        // I cannot see NewsLogs without authorization
        $I->sendGET('news-log');
        $I->seeResponseCodeIs(401);

        $I->login();

        // I can see NewsLogs with authorization
        $I->sendGET('news-log');
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType($this->responseFormat, '$.[*]');

        // I see enabled NewsLogs
        foreach ($enabledLogs as $enabledLog) {
            $I->seeResponseContainsJson([
                'id' => $enabledLog->id,
            ]);
        }

        // I don't see disabled NewsLogs
        foreach ($disabledLogs as $disabledLog) {
            $I->dontSeeResponseContainsJson([
                'id' => $disabledLog->id,
            ]);
        }
    }
}
