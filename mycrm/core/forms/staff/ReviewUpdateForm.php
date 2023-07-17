<?php

namespace core\forms\staff;

use yii\base\Model;

/**
 * @property integer $value
 * @property string $comment
 */
class ReviewUpdateForm extends Model
{
    public $value;
    public $comment;

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['value'], 'required'],

            [['value'], 'integer'],
            [['comment'], 'string'],
        ];
    }

    public function formName()
    {
        return '';
    }
}
