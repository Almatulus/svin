<?php

namespace core\forms\order;

use core\helpers\customer\CustomerHelper;
use yii\base\Model;

/**
 * @property integer $id
 * @property string $name
 * @property string $surname
 * @property string $patronymic
 * @property string $phone
 */
class OrderContactForm extends Model
{
    public $id;
    public $name;
    public $phone;

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['name', 'phone'], 'required'],
            [['id'], 'integer', 'min' => 0],
            [['phone', 'name'], 'string', 'max' => 255],
            ['phone', 'match', 'pattern' => CustomerHelper::PHONE_VALIDATE_PATTERN],
        ];
    }
}
