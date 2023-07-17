<?php

namespace api\tests\diagnosis;

use core\models\medCard\MedCardDiagnosis;
use FunctionalTester;

class DefaultCest
{
    private $responseFormat = [
        'id' => 'integer',
        'name' => 'string',
        'code' => 'string',
        'class_id' => 'integer',
    ];

    public function _before(FunctionalTester $I)
    {
    }

    public function _after(FunctionalTester $I)
    {
    }

    public function index(FunctionalTester $I)
    {
        $I->wantToTest('Diagnosis index');

        $medCardDiagnoses = $I->getFactory()->seed(5, MedCardDiagnosis::class);

        $I->sendGET('diagnosis');
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType($this->responseFormat, '$.[*]');
    }
}
