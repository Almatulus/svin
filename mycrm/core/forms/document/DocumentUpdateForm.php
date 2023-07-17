<?php

namespace core\forms\document;

use core\models\document\Document;
use core\models\Staff;

class DocumentUpdateForm extends DocumentCreateForm
{
    protected $document;

    public function __construct($id, array $attributes = [], array $config = [])
    {
        $this->document = Document::findOne($id);

        parent::__construct($this->document->document_form_id, $attributes, $config);

        $this->staff_id = $this->document->staff_id;
        $this->manager_id = $this->document->manager_id;
    }

    /**
     * @return array
     */
    protected function customAttributes(): array
    {
        $attributes = parent::customAttributes();

        return array_diff($attributes, ['customer_id']);
    }

    /**
     * @return array
     */
    protected function customAttributeLabels(): array
    {
        return [
            'manager_id' => \Yii::t('app', 'Manager ID'),
            'staff_id'   => \Yii::t('app', 'Staff ID'),
        ];
    }

    /**
     * @return array
     */
    protected function customRules(): array
    {
        return [
            [['staff_id'], 'required'],
            [['manager_id', 'staff_id'], 'integer'],
            [
                'manager_id',
                'exist',
                'targetClass'     => Staff::class,
                'targetAttribute' => 'id'
            ],
            [
                'staff_id',
                'exist',
                'targetClass'     => Staff::class,
                'targetAttribute' => 'id'
            ]
        ];
    }
}