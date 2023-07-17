<?php

namespace api\tests\medCard;

use FunctionalTester;
use core\models\company\Insurance;
use core\models\customer\CompanyCustomer;
use core\models\customer\Customer;
use core\models\customer\CustomerSource;
use core\models\medCard\MedCardToothDiagnosis;

class ToothDiagnosisCest
{
    private $responseFormat
        = [
            "id"           => "integer",
            "company_id"   => "integer",
            "name"         => "string",
            "abbreviation" => "string",
            "color"        => "string",
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
        $I->sendGET('tooth-diagnosis');
        $I->seeResponseCodeIs(401);

        $user = $I->login();

        $I->getFactory()->seed(5, MedCardToothDiagnosis::class, [
            'company_id' => $user->company_id,
        ]);
        $I->sendGET('tooth-diagnosis');
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType($this->responseFormat, '$.[*]');
    }

    public function view(FunctionalTester $I)
    {
        $I->sendGET('tooth-diagnosis');
        $I->seeResponseCodeIs(401);

        $user = $I->login();

        $model = $I->getFactory()->create(MedCardToothDiagnosis::class, [
            'company_id' => $user->company_id,
        ]);

        $I->sendGET("tooth-diagnosis/{$model->id}");
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType($this->responseFormat);
    }
}
