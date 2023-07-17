<?php

namespace core\forms\user;

use core\repositories\ConfirmKeyRepository;
use core\repositories\exceptions\NotFoundException;
use Yii;
use yii\base\Model;

/**
 * @property string $username
 * @property string $code
 * @property string $password
 */
class ResetPasswordForm extends Model
{
    public $username;
    public $code;
    public $password;
    private $confirmKeyRepository;

    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $this->confirmKeyRepository = new ConfirmKeyRepository();
    }

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['username', 'code', 'password'], 'required'],
            [['username', 'code'], 'string'],
            [['password'], 'string', 'min' => 6],
            ['code', 'validateCode']
        ];
    }

    /**
     * Validates the code.
     * This method serves as the inline validation for code.
     *
     * @param string $attribute the attribute currently being validated
     * @param array  $params    the additional name-value pairs given in the rule
     */
    public function validateCode($attribute, $params)
    {
        if ( ! $this->hasErrors()) {
            try {
                $this->confirmKeyRepository->findActiveByCodeAndUsername(
                    $this->code,
                    $this->username
                );
            } catch (NotFoundException $e) {
                $this->addError(
                    $attribute,
                    Yii::t('app', 'Incorrect username or code')
                );
            }
        }
    }
}
