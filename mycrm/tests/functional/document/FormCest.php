<?php

namespace api\tests\document;

use core\models\document\DocumentFormElement;
use FunctionalTester;
use core\models\document\DocumentForm;

class FormCest
{
    private $responseFormat = [
        'id'              => 'integer',
        'name'            => 'string',
        'has_dental_card' => 'boolean',
        'elements'        => 'array|null'
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
        $I->sendGET("document/form?expand=elements");
        $I->seeResponseCodeIs(401);

        $I->login();

        array_map(function(DocumentForm $form) use ($I) {
            return $I->getFactory()->seed(10, DocumentFormElement::class, [
                'document_form_id' => $form->id,
            ]);
        }, $I->getFactory()->seed(2, DocumentForm::class));

        $I->sendGET("document/form?expand=elements");
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType($this->responseFormat, "$.[*]");
    }

    public function view(FunctionalTester $I)
    {
        $I->sendGET("document/form/1?expand=elements");
        $I->seeResponseCodeIs(401);

        $I->login();

        $documentForm = $I->getFactory()->create(DocumentForm::class);
        $I->getFactory()->seed(10, DocumentFormElement::class, [
            'document_form_id' => $documentForm->id,
        ]);

        $I->sendGET("document/form/{$documentForm->id}?expand=elements");
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType($this->responseFormat);
    }
}
