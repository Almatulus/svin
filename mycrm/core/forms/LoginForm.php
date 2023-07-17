<?php

namespace core\forms;

use core\helpers\customer\CustomerHelper;
use core\models\user\User;
use Yii;
use yii\base\Model;

/**
 * LoginForm is the model behind the login form.
 */
class LoginForm extends Model
{
    public $username;
    public $password;

    private $_user = false;


    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            // username and password are both required
            [['username', 'password'], 'required'],
            ['username', 'match', 'pattern' => CustomerHelper::PHONE_VALIDATE_PATTERN],
            // password is validated by validatePassword()
            ['password', 'validateCompany'],
            ['password', 'validatePassword'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'username' => Yii::t('app', 'Phone'),
            'password' => Yii::t('app', 'Password'),
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
            $user = $this->getUser();

            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError($attribute, Yii::t('app', 'Incorrect username or password'));
            }
        }
    }

    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validateCompany($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();

            if (!$user) {
                $this->addError($attribute, Yii::t('app', 'Incorrect username or password'));
            } else if (!$user->company->isActive()) {
                $this->addError(
                    $attribute,
                    Yii::t('app', 'Your company disabled. Please contact us by {phone}', ['phone' => Yii::$app->params['call_phone_number']])
                );
            }
        }
    }

    /**
     * Logs in a user using the provided username and password.
     * If company set auth limit time, user can login only if he has schedule at this time.
     * @return boolean whether the user is logged in successfully
     */
    public function login()
    {
        if ($this->validate()) {
            $user = $this->getUser();
            Yii::$app->session->set('username', $user->username);

            if($user->isTimeLimitedBySchedule()){
                $schedule = $user->staff->getScheduleForNow();
                if($schedule){
                    Yii::$app->user->absoluteAuthTimeout = $schedule->remainingTime;
                }else{
                    $this->addError('username', Yii::t('app', 'No timetable for this time'));
                    return false;
                }
            }

            return Yii::$app->user->login($user, \core\models\user\User::SESSION_TIMEOUT);
        }
        return false;
    }

    /**
     * Finds user by [[username]]
     *
     * @return User|null
     */
    public function getUser()
    {
        if ($this->_user === false) {
            $this->_user = User::findByUsername($this->username);
        }

        return $this->_user;
    }
}
