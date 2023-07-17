<?php

namespace core\forms\user;

use core\models\query\UserQuery;
use core\models\user\User;
use yii\base\Model;

/**
 * @property string $username
 */
class ForgotPasswordForm extends Model
{
    public $username;

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['username'], 'required'],
            [['username'], 'string'],
            [
                ['username'],
                'exist',
                'skipOnError'     => false,
                'targetClass'     => User::className(),
                'targetAttribute' => ['username' => 'username'],
                'filter'          => function (UserQuery $query) {
                    return $query->enabled();
                },
                'message'         => \Yii::t('app', 'Incorrect phone')
            ]
        ];
    }
}