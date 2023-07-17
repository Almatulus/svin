<?php

namespace document;


use core\helpers\AppHelper;
use core\models\document\DocumentForm;
use core\models\document\DocumentFormElement;
use core\models\document\DocumentTemplate;
use core\models\medCard\MedCardToothDiagnosis;
use FunctionalTester;

class TemplateCest
{
    private $responseFormat = [
        'id'               => 'integer',
        'document_form_id' => 'integer',
        'name'             => 'string',
        'created_at'       => 'string',
        'created_by'       => 'integer',
        'values'           => 'array|null'
    ];

    public function _before(FunctionalTester $I)
    {
    }

    public function _after(FunctionalTester $I)
    {
    }

    public function index(FunctionalTester $I)
    {
        $I->sendGET('document/template');
        $I->seeResponseCodeIs(401);

        $user = $I->login();

        $I->sendGET('document/template');
        $I->seeResponseCodeIs(200);
        $I->seeResponseEquals(json_encode([]));

        $I->getFactory()->seed(2, DocumentTemplate::class, [
            'created_by' => $user->id
        ]);
        $I->sendGET('document/template');
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType($this->responseFormat, "$.[*]");
    }


    public function view(FunctionalTester $I)
    {
        $I->sendGET('document/template/1');
        $I->seeResponseCodeIs(401);

        $user = $I->login();

        $I->sendGET('document/template/1');
        $I->seeResponseCodeIs(404);

        $template = $I->getFactory()->create(DocumentTemplate::class, [
            'created_by' => $user->id
        ]);
        $I->sendGET("document/template/{$template->id}");
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType($this->responseFormat);
    }

    public function create(FunctionalTester $I)
    {
        $I->sendPOST('document/template/1');
        $I->seeResponseCodeIs(401);

        $user = $I->login();

        $I->sendPOST('document/template/0');
        $I->seeResponseCodeIs(404);

        // create diagnosis for tooth
        $diagnosis = $I->getFactory()->create(MedCardToothDiagnosis::class, [
            'company_id' => $user->company_id
        ]);
        // create document form with elements
        $documentForm = $I->getFactory()->create(DocumentForm::class, [
            'name'            => $I->getFaker()->name,
            'has_dental_card' => true,
        ]);
        $textElement = $I->getFactory()->create(DocumentFormElement::class, [
            'document_form_id' => $documentForm->id,
            'type'             => DocumentFormElement::TYPE_TEXT_INPUT,
        ]);
        $selectElement = $I->getFactory()->create(DocumentFormElement::class, [
            'document_form_id' => $documentForm->id,
            'type'             => DocumentFormElement::TYPE_SELECT,
            'options'          => AppHelper::arrayToPg(...[
                $I->getFaker()->name,
                $I->getFaker()->name,
            ])
        ]);

        $data = [
            'name'              => $I->getFaker()->name,
            $textElement->key   => $I->getFaker()->text(),
            $selectElement->key => key($selectElement->getDecodedOptions()),
            'dentalCard'        => [
                ['number' => 48, 'mobility' => 4, 'diagnosis_id' => $diagnosis->id]
            ]
        ];

        $I->sendPOST("document/template/{$documentForm->id}", $data);
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType($this->responseFormat);

        $template = DocumentTemplate::findOne(['document_form_id' => $documentForm->id]);
        verify($template)->notNull();
        verify(json_decode($template->values, true))->equals(array_diff_key($data, ['name' => '']));
    }

    public function update(FunctionalTester $I)
    {
        $I->sendPUT('document/template/1');
        $I->seeResponseCodeIs(401);

        $user = $I->login();

        $I->sendPUT('document/template/0');
        $I->seeResponseCodeIs(404);

        $anotherTemplate = $I->getFactory()->create(DocumentTemplate::class);
        $I->sendPUT("document/template/{$anotherTemplate->id}");
        $I->seeResponseCodeIs(403);

        // create document form with elements
        $documentForm = $I->getFactory()->create(DocumentForm::class, [
            'name'            => $I->getFaker()->name,
            'has_dental_card' => true,
        ]);
        $textElement = $I->getFactory()->create(DocumentFormElement::class, [
            'document_form_id' => $documentForm->id,
            'type'             => DocumentFormElement::TYPE_TEXT_INPUT,
        ]);
        $selectElement = $I->getFactory()->create(DocumentFormElement::class, [
            'document_form_id' => $documentForm->id,
            'type'             => DocumentFormElement::TYPE_SELECT,
            'options'          => AppHelper::arrayToPg(...[
                $I->getFaker()->name,
                $I->getFaker()->name,
            ])
        ]);
        $template = $I->getFactory()->create(DocumentTemplate::class, [
            'document_form_id' => $documentForm->id,
            'created_by'       => $user->id
        ]);
        // create diagnosis for tooth
        $diagnosis = $I->getFactory()->create(MedCardToothDiagnosis::class, [
            'company_id' => $user->company_id
        ]);

        $data = [
            'name'              => $I->getFaker()->name,
            $textElement->key   => $I->getFaker()->text(),
            $selectElement->key => key($selectElement->getDecodedOptions()),
            'dentalCard'        => [
                ['number' => 48, 'diagnosis_id' => $diagnosis->id, 'mobility' => 4]
            ]
        ];

        $I->sendPUT("document/template/{$template->id}", $data);
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType($this->responseFormat);

        $template = DocumentTemplate::findOne(['id' => $template->id]);
        verify(json_decode($template->values, true))->equals(array_diff_key($data, ['name' => '']));
    }

    /**
     * @group document_template
    */
    public function delete(FunctionalTester $I)
    {
        $I->sendDELETE('document/template/1');
        $I->seeResponseCodeIs(401);

        $user = $I->login();

        $I->sendDELETE('document/template/0');
        $I->seeResponseCodeIs(404);

        $anotherTemplate = $I->getFactory()->create(DocumentTemplate::class);
        $I->sendDELETE("document/template/{$anotherTemplate->id}");
        $I->seeResponseCodeIs(403);

        // create document form with elements
        $documentForm = $I->getFactory()->create(DocumentForm::class, [
            'name'            => $I->getFaker()->name,
            'has_dental_card' => true,
        ]);
        $I->getFactory()->create(DocumentFormElement::class, [
            'document_form_id' => $documentForm->id,
            'type'             => DocumentFormElement::TYPE_TEXT_INPUT,
        ]);
        $I->getFactory()->create(DocumentFormElement::class, [
            'document_form_id' => $documentForm->id,
            'type'             => DocumentFormElement::TYPE_SELECT,
            'options'          => AppHelper::arrayToPg(...[
                $I->getFaker()->name,
                $I->getFaker()->name,
            ])
        ]);
        $template = $I->getFactory()->create(DocumentTemplate::class, [
            'document_form_id' => $documentForm->id,
            'created_by'       => $user->id
        ]);

        $I->sendDELETE("document/template/{$template->id}");
        $I->seeResponseCodeIs(204);
    }
}
