<?php

namespace core\forms\warehouse\category;

use Yii;
use yii\base\Model;

/**
 * Class CategoryCreateForm
 *
 * @property string $name
 */
class CategoryCreateForm extends Model
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