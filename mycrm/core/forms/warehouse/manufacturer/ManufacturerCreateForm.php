<?php

namespace core\forms\warehouse\manufacturer;

use Yii;
use yii\base\Model;

/**
 * Class ManufacturerCreateForm
 * @package core\forms\warehouse\usage
 *
 * @property string $name
 */
class ManufacturerCreateForm extends Model
{
    public $name;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            ['name', 'required'],
            ['name', 'string'],
        ];
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'name' => Yii::t('app', 'Name'),
        ];
    }
}