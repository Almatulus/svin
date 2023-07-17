<?php

namespace frontend\modules\document\forms;

use yii\base\Model;

class OptionForm extends Model
{
    public $label;

    public function rules()
    {
        return [
            ['label', 'required'],
            ['label', 'string', 'max' => 255]
        ];
    }
}