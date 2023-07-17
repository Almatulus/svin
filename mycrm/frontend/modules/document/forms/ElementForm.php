<?php

namespace frontend\modules\document\forms;

use core\models\document\DocumentFormElement;
use core\models\document\DocumentFormGroup;
use yii\base\Model;

class ElementForm extends Model
{
    public $document_form_group_id;
    public $label;
    public $key;
    public $type;
    public $order;
    public $raw_id;
    public $options;
    public $depends_on;
    public $search_url;

    public function rules()
    {
        return [
            [['label', 'key', 'type'], 'required'],
            [['key', 'label', 'search_url', 'depends_on'], 'string'],
            ['type', 'integer'],
            ['type', 'in', 'range' => array_keys(DocumentFormElement::getTypes())],
            [['order', 'raw_id'], 'integer', 'min' => 0],

            ['document_form_group_id', 'integer'],
            ['document_form_group_id', 'exist', 'targetClass' => DocumentFormGroup::class, 'targetAttribute' => 'id'],

            [
                'options',
                'required',
                'when' => function (self $model) {
                    return ($model->type == DocumentFormElement::TYPE_SELECT ||
                            $model->type == DocumentFormElement::TYPE_RADIOLIST) &&
                        empty($model->search_url);
                }
            ],
            [
                'options',
                'validateOptions',
                'when' => function (self $model) {
                    return ($model->type == DocumentFormElement::TYPE_SELECT ||
                            $model->type == DocumentFormElement::TYPE_RADIOLIST) &&
                        empty($model->search_url);
                }
            ]
        ];
    }

    public function validateOptions($attribute, $params)
    {
        foreach ($this->$attribute as $key => $option) {
            $optionForm = new OptionForm([
                'label' => $option['label'] ?? null
            ]);
            if (!$optionForm->validate()) {
                foreach ($optionForm->firstErrors as $attributeName => $messageError) {
                    $this->addError("{$attribute}[$key][$attributeName]", $messageError);
                }
            }
        }
    }
}
