<?php

namespace frontend\modules\document\forms;

use core\models\document\DocumentForm;
use core\models\document\DocumentFormElement;
use yii\base\Model;

class ElementsForm extends Model
{
    public $elements;

    protected $documentForm;

    /**
     * ElementsForm constructor.
     * @param int $id
     * @param array $config
     */
    public function __construct(int $id, array $config = [])
    {
        $this->documentForm = DocumentForm::findOne($id);

        if (!$this->documentForm) {
            throw new \InvalidArgumentException("Invalid id");
        }

        $elements = $this->documentForm->getElements()->orderBy('order ASC')->all();
        array_walk($elements, function (DocumentFormElement $element, $key) {
            $this->elements[$key] = $element->toArray();
            $this->elements[$key]["document_form_group_id"] = $element->document_form_group_id;
            $this->elements[$key]["type"] = $element->type;
        });

        parent::__construct($config);
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['elements'], 'required'],
            ['elements', 'validateElements'],
            [
                'elements',
                'filter',
                'filter' => function ($elements) {
                    foreach ($elements as $key => $elementData) {
                        if (!empty($elementData['options'])) {
                            $elements[$key]['options'] = array_map(function ($option) {
                                return $option['label'];
                            }, $elementData['options']);
                        }
                        if (!empty($elements[$key]["key"])) {
                            $elements[$key]["key"] = trim($elements[$key]["key"]);
                        }
                    }
                    return $elements;
                }
            ]
        ];
    }

    /**
     * @param $attribute
     * @param $params
     */
    public function validateElements($attribute, $params)
    {
        foreach ($this->{$attribute} as $key => $element) {
            $elementForm = new ElementForm([
                'document_form_group_id' => $element['document_form_group_id'] ?? null,
                'label'                  => $element['label'] ?? null,
                'key'                    => $element['key'] ?? null,
                'options'                => $element['options'] ?? null,
                'order'                  => $element['order'] ?? null,
                'type'                   => $element['type'] ?? null,
                'raw_id'                 => $element['raw_id'] ?? null,
                'depends_on'             => $element['depends_on'] ?? null,
                'search_url'             => $element['search_url'] ?? null,
            ]);
            if (!$elementForm->validate()) {
                foreach ($elementForm->firstErrors as $attributeName => $messageError) {
                    $this->addError("{$attribute}[$key][$attributeName]", $messageError);
                }
            }
        }
    }

    /**
     * @return DocumentForm
     */
    public function getDocumentForm(): DocumentForm
    {
        return $this->documentForm;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->documentForm->id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->documentForm->name;
    }

}
