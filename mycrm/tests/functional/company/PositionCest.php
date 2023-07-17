<?php

namespace tests\functional\customer;

use core\models\document\DocumentForm;
use core\models\medCard\MedCardComment;
use FunctionalTester;
use core\models\company\CompanyPosition;

class PositionCest
{
    private $responseFormat = [
        'id'          => 'integer',
        'name'        => 'string',
        'description' => 'string|null',
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
        $I->wantToTest('Company positions index');
        $I->sendGET('company/position');
        $I->seeResponseCodeIs(401);

        $user = $I->login();

        $I->getFactory()->seed(5, CompanyPosition::class, [
            'company_id' => $user->company_id,
        ]);
        $I->sendGET('company/position');
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType($this->responseFormat, '$.[*]');
    }

    public function view(FunctionalTester $I)
    {
        $I->wantToTest('Company positions view');
        $I->sendGET('company/position');
        $I->seeResponseCodeIs(401);

        $user = $I->login();

        // I see notDeleted my CompanyPosition
        $myCompanyPosition = $I->getFactory()->create(CompanyPosition::class, [
            'company_id' => $user->company_id,
        ]);

        $I->sendGET("company/position/{$myCompanyPosition->id}");
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType($this->responseFormat);


        // I don't see notDeleted other CompanyPosition
        $otherCompanyPosition = $I->getFactory()->create(CompanyPosition::class, []);

        $I->sendGET("company/position/{$otherCompanyPosition->id}");
        $I->seeResponseCodeIs(404);


        // I don't see deleted my CompanyPosition
        $deletedCompanyPosition = $I->getFactory()->create(CompanyPosition::class, [
            'company_id' => $user->company_id,
            'deleted_time' => gmdate('Y-m-d H:i:s'),
        ]);

        $I->sendGET("company/position/{$deletedCompanyPosition->id}");
        $I->seeResponseCodeIs(404);
    }

    public function create(FunctionalTester $I)
    {
        $I->wantToTest('Company position create');
        $I->sendPOST('company/position');
        $I->seeResponseCodeIs(401);

        $I->login();

        /** @var DocumentForm[] $documentForms */
        $documentForms = $I->getFactory()->seed(2, DocumentForm::class);

        $I->amGoingTo('Create company position with invalid data');
        $I->sendPOST('company/position', ['name' => '']);
        $I->seeResponseCodeIs(422);

        $I->amGoingTo('Create company position with valid data');
        $I->sendPOST('company/position', [
            'name'        => $I->getFaker()->name,
            'description' => $I->getFaker()->text(10),
            'documentForms' => array_map(function(DocumentForm $model) {
                return $model->id;
            }, $documentForms),
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType($this->responseFormat);
    }

    public function update(FunctionalTester $I)
    {
        $I->wantToTest('Company position update');

        $I->amGoingTo('Update company position without valid credentials');
        $I->sendPUT('company/position/1');
        $I->seeResponseCodeIs(401);

        $user = $I->login();

        $I->amGoingTo('Update non-existing company position');
        $I->sendPUT('company/position/0');
        $I->seeResponseCodeIs(404);

        $anotherModel = $I->getFactory()->create(CompanyPosition::class);
        $I->amGoingTo('Update position which does not belong to my company');
        $I->sendPUT("company/position/{$anotherModel->id}", ['name' => '']);
        $I->seeResponseCodeIs(404);

        $model = $I->getFactory()->create(CompanyPosition::class, ['company_id' => $user->company_id,]);
        $I->amGoingTo('Update company position with invalid data');
        $I->sendPUT("company/position/{$model->id}", ['name' => '']);
        $I->seeResponseCodeIs(422);


        /** @var DocumentForm[] $documentForms */
        $documentForms = $I->getFactory()->seed(2, DocumentForm::class);

        $I->amGoingTo('Update company position with valid data');
        $I->sendPUT("company/position/{$model->id}", [
            'name'        => $I->getFaker()->name,
            'description' => $I->getFaker()->text(10),
            'documentForms' => array_map(function(DocumentForm $model) {
                return $model->id;
            }, $documentForms),
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType($this->responseFormat);
    }
}
