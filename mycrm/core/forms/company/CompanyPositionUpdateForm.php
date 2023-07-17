<?php

namespace core\forms\company;

use core\models\company\CompanyPosition;
use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;

/**
 * @property string $name
 * @property string $description
 * @property array $categories
 * @property array $documentForms
 */
class CompanyPositionUpdateForm extends Model
{
    public $name;
    public $description;
    public $categories;
    public $documentForms;

    public function __construct(CompanyPosition $companyPosition, array $config = [])
    {
        parent::__construct($config);

        $this->name = $companyPosition->name;
        $this->description = $companyPosition->description;
        $this->categories = ArrayHelper::getColumn($companyPosition->medCardCommentCategories, 'id');
        $this->documentForms = ArrayHelper::getColumn($companyPosition->documentForms, 'id');
    }

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