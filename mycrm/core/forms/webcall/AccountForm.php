<?php

namespace core\forms\webcall;

use core\models\division\Division;
use core\models\webcall\query\WebcallAccountQuery;
use core\models\webcall\WebcallAccount;
use Yii;
use yii\base\Model;

class AccountForm extends Model
{
    public $division_id;
    public $email;
    public $name;
    public $password;
    public $password_confirm;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['division_id', 'name', 'email', 'password', 'password_confirm'], 'required'],
            [['name'], 'string', 'max' => 255],
            [['email'], 'email'],
            [['division_id'], 'default', 'value' => null],
            [['division_id'], 'integer'],
            [
                ['division_id'],
                'exist',
                'skipOnError'     => true,
                'targetClass'     => Division::className(),
                'targetAttribute' => ['division_id' => 'id']
            ],

            [['password', 'password_confirm'], 'string', 'min' => 6, 'max' => 32],
            [['password_confirm'], 'compare', 'compareAttribute' => 'password', 'operator' => '==='],

            [
                'email',
                'unique',
                'targetClass' => WebcallAccount::class,
                'filter'      => function (WebcallAccountQuery $query) {
                    return $query->company();
                },
                'message'     => "Для данного email уже создан аккаунт"
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'division_id'      => Yii::t('app', 'Division ID'),
            'name'             => Yii::t('app', 'Name'),
            'email'            => Yii::t('app', 'Email'),
            'password'         => Yii::t('app', 'Password'),
            'password_confirm' => Yii::t('app', 'Password Repeat'),
        ];
    }
}