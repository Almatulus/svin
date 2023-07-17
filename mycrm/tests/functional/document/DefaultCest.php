<?php

namespace api\tests\document;

use core\helpers\AppHelper;
use core\models\customer\CompanyCustomer;
use core\models\division\Division;
use core\models\division\DivisionService;
use core\models\document\Document;
use core\models\document\DocumentForm;
use core\models\document\DocumentFormElement;
use core\models\document\DocumentService;
use core\models\document\DocumentValue;
use core\models\medCard\MedCardToothDiagnosis;
use core\models\Staff;
use FunctionalTester;

class DefaultCest
{
    private $responseFormat = [
        'id'               => 'integer',
        'customer_id'      => 'integer',
        'document_form_id' => 'integer',
        'manager_id'       => 'integer',
        'staff_id'         => 'integer',
        'created_at'       => 'string',
        'customer'         => 'array',
        'dentalCard'       => 'array|null',
        'services'         => 'array|null',
        'values'           => 'array|null',
    ];

    public function _before(FunctionalTester $I)
    {
    }

    public function _after(FunctionalTester $I)
    {
    }

    public function index(FunctionalTester $I)
    {
        $I->sendGET('document');
        $I->seeResponseCodeIs(401);

        $user = $I->login();

        $companyCustomer = $I->getFactory()->create(CompanyCustomer::class, [
            'company_id' => $user->company_id
        ]);
        $I->getFactory()->seed(2, Document::class, [
            'company_customer_id' => $companyCustomer->id
        ]);

        $I->sendGET('document?expand=customer,dentalCard,services,values');
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType($this->responseFormat, '$.[*]');
    }

    public function view(FunctionalTester $I)
    {
        $I->sendGET('document/1');
        $I->seeResponseCodeIs(401);

        $user = $I->login();

        $companyCustomer = $I->getFactory()->create(CompanyCustomer::class, [
            'company_id' => $user->company_id
        ]);
        $document = $I->getFactory()->create(Document::class, [
            'company_customer_id' => $companyCustomer->id
        ]);

        $I->sendGET("document/{$document->id}?expand=customer,dentalCard,services,values");
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType($this->responseFormat);
    }

    // tests
    public function create(FunctionalTester $I)
    {
        $I->checkLogin('document');

        $user = $I->login();

        $I->sendPOST('document/1');
        $I->seeResponseCodeIs(404);

        $documentForm = $I->getFactory()->create(DocumentForm::class, [
            'has_dental_card' => true,
            'has_services'    => true
        ]);
        $companyCustomer = $I->getFactory()->create(CompanyCustomer::class, [
            'company_id' => $user->company_id
        ]);
        $division = $I->getFactory()->create(Division::class, ['company_id' => $user->company_id]);
        $services = $I->getFactory()->seed(2, DivisionService::class);
        $staff = $I->getFactory()->create(Staff::class);
        $staff->link('divisions', $division);
        $textElement = $I->getFactory()->create(DocumentFormElement::class, [
            'key'              => 'text',
            'document_form_id' => $documentForm->id,
            'type'             => DocumentFormElement::TYPE_TEXT_INPUT
        ]);
        $selectElement = $I->getFactory()->create(DocumentFormElement::class, [
            'key'              => 'select',
            'document_form_id' => $documentForm->id,
            'type'             => DocumentFormElement::TYPE_SELECT,
            'options'          => AppHelper::arrayToPg(...[
                $I->getFaker()->text(5),
                $I->getFaker()->text(5),
                $I->getFaker()->text(5)
            ])
        ]);
        $checkboxElement = $I->getFactory()->create(DocumentFormElement::class, [
            'key'              => 'checkbox',
            'document_form_id' => $documentForm->id,
            'type'             => DocumentFormElement::TYPE_CHECKBOX
        ]);
        $checkboxListElement = $I->getFactory()->create(DocumentFormElement::class, [
            'key'              => 'checkbox_list',
            'document_form_id' => $documentForm->id,
            'type'             => DocumentFormElement::TYPE_CHECKBOX_LIST,
            'options'          => AppHelper::arrayToPg(...[
                $I->getFaker()->text(5),
                $I->getFaker()->text(5),
                $I->getFaker()->text(5)
            ])
        ]);
        $diagnosis = $I->getFactory()->create(MedCardToothDiagnosis::class, [
            'company_id' => $user->company_id
        ]);

        $I->sendPOST("document/{$documentForm->id}");
        $I->seeResponseCodeIs(422);

        $text = $I->getFaker()->text();

        $selectElementOptions = $selectElement->getDecodedOptions();
        $I->sendPOST("document/{$documentForm->id}?expand=customer,dentalCard,services,values", [
            'customer_id'             => $companyCustomer->id,
            'manager_id'              => $staff->id,
            'staff_id'                => $staff->id,
            $textElement->key         => $text,
            $selectElement->key       => reset($selectElementOptions),
            $checkboxElement->key     => '1',
            $checkboxListElement->key => [0, 2],
            'dentalCard'              => [
                [
                    'number'       => 47,
                    'diagnosis_id' => $diagnosis->id
                ],
                [
                    'number'       => 46,
                    'diagnosis_id' => $diagnosis->id,
                    'mobility'     => 10
                ],
            ],
            'services'                => [
                [
                    'service_id' => $services[0]->id,
                    'quantity'   => 1,
                    'price'      => $services[0]->price,
                    'discount'   => 0
                ],
                [
                    'service_id' => $services[1]->id,
                    'quantity'   => 1,
                    'price'      => $services[1]->price,
                    'discount'   => 0
                ],
            ]
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType($this->responseFormat);
        $I->seeRecord(Document::class, [
            'company_customer_id' => $companyCustomer->id,
            'document_form_id'    => $documentForm->id
        ]);

        $I->seeRecord(DocumentValue::class, ['document_form_element_id' => $textElement->id]);
        $I->seeRecord(DocumentValue::class, ['document_form_element_id' => $selectElement->id]);
        $I->seeRecord(DocumentValue::class, ['document_form_element_id' => $checkboxElement->id]);
        $I->seeRecord(DocumentValue::class, [
            'document_form_element_id' => $checkboxListElement->id,
            'value'                    => json_encode(["0", "2"])
        ]);
        $I->seeRecord(DocumentService::class, [
            'service_id' => $services[0]->id,
            'price'      => $services[0]->price,
            'quantity'   => 1
        ]);
        $I->seeRecord(DocumentService::class, [
            'service_id' => $services[1]->id,
            'price'      => $services[1]->price,
            'quantity'   => 1
        ]);
    }

    public function update(FunctionalTester $I)
    {
        $I->sendPUT('document/1');
        $I->see(401);

        $user = $I->login();

        $I->sendPUT('document/1');
        $I->seeResponseCodeIs(404);

        $services = $I->getFactory()->seed(2, DivisionService::class);

        $documentForm = $I->getFactory()->create(DocumentForm::class, [
            'has_dental_card' => true,
            'has_services'    => true
        ]);
        $textElement = $I->getFactory()->create(DocumentFormElement::class, [
            'key'              => 'text',
            'document_form_id' => $documentForm->id,
            'type'             => DocumentFormElement::TYPE_TEXT_INPUT
        ]);
        $diagnosis = $I->getFactory()->create(MedCardToothDiagnosis::class, [
            'company_id' => $user->company_id
        ]);
        $document = $I->getFactory()->create(Document::class, [
            'document_form_id' => $documentForm->id
        ]);
        $I->getFactory()->create(DocumentValue::class, [
            'document_id'              => $document->id,
            'document_form_element_id' => $textElement->id,
            'value'                    => $I->getFaker()->text
        ]);

        $text = $I->getFaker()->text();
        $I->sendPUT("document/{$document->id}?expand=customer,dentalCard,services,values", [
            $textElement->key => $text,
            'dentalCard'      => [
                [
                    'number'       => 47,
                    'diagnosis_id' => $diagnosis->id
                ],
                [
                    'number'       => 46,
                    'diagnosis_id' => $diagnosis->id,
                    'mobility'     => 10
                ],
            ],
            'services'        => [
                [
                    'service_id' => $services[0]->id,
                    'quantity'   => 1,
                    'price'      => $services[0]->price,
                    'discount'   => 0
                ],
                [
                    'service_id' => $services[1]->id,
                    'quantity'   => 1,
                    'price'      => $services[1]->price,
                    'discount'   => 0
                ],
            ]
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType($this->responseFormat);
        $I->seeRecord(DocumentValue::class, ['document_form_element_id' => $textElement->id]);
        $I->seeRecord(DocumentService::class, [
            'service_id' => $services[0]->id,
            'price'      => $services[0]->price,
            'quantity'   => 1
        ]);
        $I->seeRecord(DocumentService::class, [
            'service_id' => $services[1]->id,
            'price'      => $services[1]->price,
            'quantity'   => 1
        ]);
    }
}
