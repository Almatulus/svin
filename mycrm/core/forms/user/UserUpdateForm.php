<?php

namespace core\forms\user;

use core\helpers\customer\CustomerHelper;
use core\models\company\Company;
use core\models\user\User;
use core\models\rbac\AuthAssignment;
use core\models\rbac\AuthItem;
use Yii;
use yii\helpers\ArrayHelper;

class UserUpdateForm extends UserForm
{
    public $user;

    /**
     * UserUpdateForm constructor.
     * @param User $user
     * @param array $config
     */
    public function __construct(User $user, $config = [])
    {
        $this->user = $user;
        $this->attributes = $user->attributes;
        parent::__construct($config);
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        $roles           = ArrayHelper::getColumn(AuthItem::getRoles(), 'name');
        $auth_assignment = AuthAssignment::find()->where(['user_id' => $this->user->id])->one();
        if ($auth_assignment && in_array($auth_assignment->item_name, $roles))
        {
            $this->role = $auth_assignment->item_name;
        }

        $this->user_permissions = AuthAssignment::find()->select("item_name")->where(['user_id' => $this->user->id])->column();
    }

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
            ['user_permissions', 'safe'],
            ['status', 'boolean'],
            ['status', 'default', 'value' => User::STATUS_ENABLED],
            ['company_id', 'exist', 'targetClass' => Company::className(), 'targetAttribute' => ['company_id'=>'id']]
        ];
    }

}
