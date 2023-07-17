<?php

namespace core\forms;

use Yii;
use yii\base\Model;

/**
 * @property string $name
 * @property string $description
 * @property array $documentForms
 */
class PositionCreateForm extends Model
{
    public $name;
    public $description;
    public $documentForms = [];

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            ['name', 'string', 'max' => 255],
            [['documentForms'], 'each', 'rule' => ['integer']],
            ['description', 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'name' => Yii::t('app', 'Name'),
            'description' => Yii::t('app', 'Description'),
            'documentForms' => Yii::t('app', 'Company Documents'),
        ];
    }
}