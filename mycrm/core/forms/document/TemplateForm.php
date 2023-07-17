<?php

namespace core\forms\document;

/**
 * Class TemplateCreateForm
 * @package core\forms\document
 *
 * @property string $name
 */
class TemplateForm extends DocumentCreateForm
{
    /**
     * @return array
     */
    protected function customAttributes(): array
    {
        return ['name'];
    }

    /**
     * @return array
     */
    protected function customAttributeLabels(): array
    {
        return ['name' => \Yii::t('app', 'Name')];
    }

    /**
     * @return array
     */
    protected function customRules(): array
    {
        return [
            ['name', 'required'],
            ['name', 'string', 'max' => 255]
        ];
    }
}