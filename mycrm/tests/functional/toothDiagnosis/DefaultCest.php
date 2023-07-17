<?php

namespace api\tests\toothDiagnosis;

use FunctionalTester;
use core\helpers\medCard\MedCardToothHelper;
use core\models\medCard\MedCardTooth;
use core\models\medCard\MedCardToothDiagnosis;

class DefaultCest
{
    private $responseFormat = [
        'id'           => 'integer',
        'color'        => 'string',
        'name'         => 'string',
        'abbreviation' => 'string',
        'company_id'   => 'integer'
    ];

    public function _before(FunctionalTester $I)
    {
    }

    public function _after(FunctionalTester $I)
    {
    }

    public function index(FunctionalTester $I)
    {
        $I->wantToTest('Tooth diagnosis index');

        $I->sendGET('tooth-diagnosis');
        $I->seeResponseCodeIs(401);

        $user = $I->login();

        $I->getFactory()->seed(2, MedCardToothDiagnosis::class, ['company_id' => $user->company_id]);
        $I->sendGET('tooth-diagnosis');
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType($this->responseFormat);
    }

    public function view(FunctionalTester $I)
    {
        $I->wantToTest('Tooth diagnosis view');

        $I->sendGET('tooth-diagnosis/1');
        $I->seeResponseCodeIs(401);

        $user = $I->login();
        $I->sendGET('tooth-diagnosis/0');
        $I->seeResponseCodeIs(404);

        $toothDiagnosis = $I->getFactory()->create(MedCardToothDiagnosis::class);
        $I->sendGET("tooth-diagnosis/{$toothDiagnosis->id}");
        $I->seeResponseCodeIs(403);

        $toothDiagnosis = $I->getFactory()->create(MedCardToothDiagnosis::class, ['company_id' => $user->company_id]);
        $I->sendGET("tooth-diagnosis/{$toothDiagnosis->id}");
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType($this->responseFormat);
    }

    public function create(FunctionalTester $I)
    {
        $I->wantToTest('Tooth diagnosis create');

        $I->sendPOST('tooth-diagnosis');
        $I->seeResponseCodeIs(401);

        $I->login();

        $I->sendPOST("tooth-diagnosis");
        $I->seeResponseCodeIs(422);

        $I->sendPOST("tooth-diagnosis", [
            'name'         => $I->getFaker()->name,
            'abbreviation' => $I->getFaker()->randomElement(MedCardToothHelper::getDiagnosisAbbreviations()),
            'color'        => $I->getFaker()->hexColor,
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType($this->responseFormat);
    }

    public function update(FunctionalTester $I)
    {
        $I->wantToTest('Tooth diagnosis update');

        $I->sendPUT('tooth-diagnosis/1');
        $I->seeResponseCodeIs(401);

        $user = $I->login();
        $I->sendPUT('tooth-diagnosis/0');
        $I->seeResponseCodeIs(404);

        $toothDiagnosis = $I->getFactory()->create(MedCardToothDiagnosis::class);
        $I->sendPUT("tooth-diagnosis/{$toothDiagnosis->id}");
        $I->seeResponseCodeIs(403);

        $toothDiagnosis = $I->getFactory()->create(MedCardToothDiagnosis::class, ['company_id' => $user->company_id]);
        $I->sendPUT("tooth-diagnosis/{$toothDiagnosis->id}", [
            "name" => ""
        ]);
        $I->seeResponseCodeIs(422);

        $I->sendPUT("tooth-diagnosis/{$toothDiagnosis->id}", [
            'name'         => $I->getFaker()->name,
            'abbreviation' => $I->getFaker()->randomElement(MedCardToothHelper::getDiagnosisAbbreviations()),
            'color'        => $I->getFaker()->hexColor,
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType($this->responseFormat);
    }

    public function delete(FunctionalTester $I)
    {
        $I->wantToTest('Tooth diagnosis delete');

        $I->sendDELETE('tooth-diagnosis/1');
        $I->seeResponseCodeIs(401);

        $user = $I->login();
        $I->sendDELETE('tooth-diagnosis/0');
        $I->seeResponseCodeIs(404);

        $toothDiagnosis = $I->getFactory()->create(MedCardToothDiagnosis::class);
        $I->sendDELETE("tooth-diagnosis/{$toothDiagnosis->id}");
        $I->seeResponseCodeIs(403);

        // create tooth diagnosis and link with tooth diagnosis of medical card. Expect response about error, because
        // diagnosis cannot be deleted if any entity has reference of it
        $toothDiagnosis = $I->getFactory()->create(MedCardToothDiagnosis::class, ['company_id' => $user->company_id]);
        $medCardToothDiagnosis = $I->getFactory()->create(MedCardTooth::class,
            ['teeth_diagnosis_id' => $toothDiagnosis->id]);
        $I->sendDELETE("tooth-diagnosis/{$toothDiagnosis->id}");
        $I->seeResponseCodeIs(500);

        $medCardToothDiagnosis->delete();
        $I->sendDELETE("tooth-diagnosis/{$toothDiagnosis->id}");
        $I->seeResponseCodeIs(204);
    }
}
