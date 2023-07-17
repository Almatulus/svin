<?php

namespace core\forms\user;

use core\helpers\customer\CustomerHelper;
use core\models\company\Company;
use core\models\user\User;
use core\models\rbac\AuthItem;
use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;

class UserForm extends Model
{
    public $company_id;
    public $password;
    public $role;
    public $status;
    public $username;
    public $user_permissions;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['username', 'company_id'], 'required'],
            [['company_id'], 'integer'],
            [['password', 'role', 'username'], 'string', 'max' => 255],
            ['password', 'string', 'min' => 6],
            ['role', 'in', 'range' => ArrayHelper::getColumn(AuthItem::getRoles(), 'name')],
            ['username', 'match', 'pattern' => CustomerHelper::PHONE_VALIDATE_PATTERN],
            ['status', 'boolean'],
            ['status', 'default', 'value' => User::STATUS_ENABLED],
            ['user_permissions', 'safe'],
            [['username'], 'unique', 'targetClass' => User::className(), 'targetAttribute' => 'username'],
            ['company_id', 'exist', 'targetClass' => Company::className(), 'targetAttribute' => ['company_id'=>'id']]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'username' => Yii::t('app', 'Username'),
            'password' => Yii::t('app', 'Password'),
            'company_id' => Yii::t('app', 'Company ID'),
            'role' => Yii::t('app', 'Role'),
            'status' => Yii::t('app', 'Status'),
            'user_permissions' => Yii::t('app', 'Staff Permissions')
        ];
    }

}
