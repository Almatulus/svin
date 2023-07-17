<?php

namespace core\forms\company;

use Yii;
use yii\base\Model;

/**
 * @property string $name
 * @property string $description
 * @property array $categories
 * @property array $documentForms
 */
class CompanyPositionCreateForm extends Model
{
    public $name;
    public $description;
    public $categories = [];
    public $documentForms = [];

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            ['name', 'string', 'max' => 255],
            [['categories'], 'each', 'rule' => ['integer']],
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
            'categories' => Yii::t('app', 'Med Card Comment Categories'),
            'documentForms' => Yii::t('app', 'Company Documents'),
        ];
    }
}