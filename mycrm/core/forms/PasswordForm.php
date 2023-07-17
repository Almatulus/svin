<?php

namespace core\forms;

use core\models\user\User;
use Yii;
use yii\base\Model;

/**
 * LoginForm is the model behind the login form.
 */
class PasswordForm extends Model
{
    public $password;
    public $new_password;
    public $password_repeat;

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            // username and password are both required
            [['password', 'new_password', 'password_repeat'], 'required'],
            [['password', 'new_password'], 'string', 'min' => 6, 'max' => 255],
            // password is validated by validatePassword()
            ['password', 'validatePassword'],
            ['password_repeat', 'compare', 'compareAttribute'=>'new_password',
                'message' => Yii::t('app', 'Passwords does not match')],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'password' => Yii::t('app', 'Current Password'),
            'new_password' => Yii::t('app', 'New Password'),
            'password_repeat' => Yii::t('app', 'Password Repeat'),
        ];
    }

    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            /* @var User $user */
            $user = Yii::$app->user->identity;

            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError($attribute, Yii::t('app', 'Incorrect password'));
            }
        }
    }
}
