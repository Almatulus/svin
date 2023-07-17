<?php

namespace services\document;

use core\helpers\AppHelper;
use core\models\document\DocumentForm;
use core\models\document\DocumentFormElement;
use core\services\document\FormService;
use frontend\modules\document\forms\ElementsForm;

class FormServiceTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;
    /** @var FormService */
    private $_service;

    public function testCreateElements()
    {
        $documentForm = $this->tester->getFactory()->create(DocumentForm::class);

        $elementsForm = new ElementsForm($documentForm->id);
        $elementsForm->elements = [
            [
                'label' => $this->tester->getFaker()->text(5),
                'key'   => $this->tester->getFaker()->text(5),
                'type'  => DocumentFormElement::TYPE_TEXT_INPUT
            ],
            [
                'label'   => $this->tester->getFaker()->text(5),
                'key'     => $this->tester->getFaker()->text(5),
                'type'    => DocumentFormElement::TYPE_SELECT,
                'options' => [
                    $this->tester->getFaker()->text,
                    $this->tester->getFaker()->text,
                ]
            ],
        ];

        $elements = $this->_service->createElements($elementsForm);

        foreach ($elementsForm->elements as $elementData) {
            $this->tester->canSeeRecord(DocumentFormElement::class, [
                'document_form_id' => $documentForm->id,
                'label'            => $elementData['label'],
                'key'              => $elementData['key'],
                'type'             => $elementData['type'],
                'options'          => isset($elementData['options'])
                    ? AppHelper::arrayToPg(...$elementData['options'])
                    : null
            ]);
        }
    }

    public function testDuplicate()
    {
        $documentForm = $this->tester->getFactory()->create(DocumentForm::class);
        $elements = $this->tester->getFactory()->seed(3, DocumentFormElement::class, [
            'document_form_id' => $documentForm->id
        ]);

        $duplicatedForm = $this->_service->duplicate($documentForm);

        verify($duplicatedForm)->notNull();
        verify($duplicatedForm->id)->notNull();
        $this->tester->canSeeRecord(DocumentForm::class, array_merge($documentForm->attributes, [
            'id'   => $duplicatedForm->id,
            'name' => $documentForm->name . ' (COPY)'
        ]));

        foreach ($elements as $element) {
            $this->tester->canSeeRecord(DocumentFormElement::class, [
                'document_form_id' => $duplicatedForm->id,
                'label'            => $element->label,
                'key'              => $element->key,
                'type'             => $element->type,
                'options'          => $element->options
            ]);
        }
    }

    public function testGetDocumentFormsList()
    {
        $data = [
            1 => $this->tester->getFaker()->text(10),
            2 => $this->tester->getFaker()->text(10),
            3 => $this->tester->getFaker()->text(10)
        ];

        foreach ($data as $key => $name) {
            $this->tester->getFactory()->create(DocumentForm::class, [
                'id'   => $key,
                'name' => $name
            ]);
        }

        $mappedForms = $this->_service->getDocumentFormsList();

        verify($data)->equals($mappedForms);
    }

    protected function _before()
    {
        $this->_service = \Yii::createObject(FormService::class);
    }

    protected function _after()
    {
    }

}