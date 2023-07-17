<?php

namespace core\forms;

use core\models\Position;
use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;

/**
 * @property string $name
 * @property string $description
 * @property array $documentForms
 */
class PositionUpdateForm extends Model
{
    public $name;
    public $description;
    public $documentForms;

    public function __construct(Position $position, array $config = [])
    {
        parent::__construct($config);

        $this->name = $position->name;
        $this->description = $position->description;
        $this->documentForms = ArrayHelper::getColumn($position->documentForms, 'id');
    }

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