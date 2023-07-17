<?php

namespace core\forms;

use core\helpers\customer\CustomerHelper;
use core\helpers\Security;
use core\models\user\User;
use core\models\customer\CustomerRequest;
use himiklab\yii2\recaptcha\ReCaptchaValidator;
use Yii;
use yii\base\Model;

/**
 * RestoreForm is the model behind the restore form.
 */
class RestoreForm extends Model
{
    public $phone;
    public $reCaptcha;
    public $password;
    public $repassword;
    public $code;

    private $_user = false;

    /**
     * Scenarios
     */
    const SCENARIO_PHONE = 'phone';
    const SCENARIO_CODE = 'code';
    const SCENARIO_PASS = 'password';


    public function scenarios()
    {
        $scenarios                       = parent::scenarios();
        $scenarios[self::SCENARIO_PHONE] = ['phone', 'recaptcha'];
        $scenarios[self::SCENARIO_CODE]  = ['phone', 'code'];
        $scenarios[self::SCENARIO_PASS]  = ['phone', 'password', 'repassword'];
        return $scenarios;
    }


    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['password', 'repassword', 'code', 'phone'], 'required'],
            [['password', 'repassword'], 'trim'],
            [['password', 'repassword'], 'string', 'min' => 6, 'max' => 30],
            ['repassword', 'compare', 'compareAttribute' => 'password'],
            [['phone'], 'string'],
            [['phone'], 'validatePhone'],
            ['phone', 'match', 'pattern' => CustomerHelper::PHONE_VALIDATE_PATTERN],
            ['code', 'string', 'max' => 20],
            ['code', 'validateCode'],
            [['reCaptcha'], ReCaptchaValidator::className(), 'secret' => getenv('RE_CAPTCHA_PRIVATE')]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'phone' => Yii::t('app', 'Phone'),
            'password' => Yii::t('app', 'Password'),
            'repassword' => Yii::t('app', 'Repassword'),
            'code' => Yii::t('app', 'Code')
        ];
    }

    /**
     * @param $attribute
     * @param $params
     */
    public function validatePhone($attribute, $params)
    {
        if (!$this->user) {
            $this->addError($attribute, Yii::t('app', 'Incorrect phone'));
        }
    }

    /**
     * @param $attribute
     * @param $params
     */
    public function validateCode($attribute, $params)
    {
        if ($this->user) {
            $validate = Yii::$app->security->validatePassword(
                $this->user->salt . $this->code, $this->user->forgot_hash
            );
            if (!$validate) {
                $this->addError($attribute, Yii::t('app', 'Incorrect key'));
            }
        }
    }

    /**
     *  generates random key from figures
     */
    public function generateCode()
    {
        $this->code = Security::random_str(6, "0123456789");
    }

    /**
     *  generates hash
     * @throws \yii\base\Exception
     */
    public function generateForgotHash()
    {
        if ($this->user) {
            $this->generateCode();
            $this->user->forgot_hash = Yii::$app->security->generatePasswordHash($this->user->salt . $this->code);
            return $this->user->save();
        }
        return false;
    }

    public function resetForgotHash() 
    {
        if ($this->user) {
            $this->user->forgot_hash = null;
            return $this->user->save();
        }
        return false;
    }

    /**
     * Send code to user via sms
     */
    public function sendCode()
    {
        CustomerRequest::sendNotAssignedSMS($this->phone, "Код для восстановления пароля: " . $this->code, $this->user->company_id);
    }

    public function recovery()
    {
        if ($this->user) {
            $this->user->editPassword($this->password);
            if ($this->user->save()) {
                return true;
            }
        }
        return false;
    }

    public function getErrorMessage() {
        $message = "";
        foreach ($this->errors as $key => $error) {
            $message .= $error[0] . '<br>';
        }
        return $message;
    }

    /**
     * Finds user by [[username]]
     *
     * @return User|null
     */
    public function getUser()
    {
        if (!$this->_user) {
            $this->_user = User::findByUsername($this->phone);
        }

        return $this->_user;
    }
}
