<?php

namespace core\models\user;

use common\components\HistoryBehavior;
use core\helpers\customer\CustomerHelper;
use core\helpers\Security;
use core\helpers\user\UserHelper;
use core\models\company\Company;
use core\models\ConfirmKey;
use core\models\customer\CustomerRequest;
use core\models\query\UserQuery;
use core\models\rbac\AuthAssignment;
use core\models\rbac\AuthItem;
use core\models\Staff;
use Yii;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\web\IdentityInterface;

/**
 * This is the model class for table "{{%users}}".
 *
 * @property integer $id
 * @property string $username
 * @property string $password_hash
 * @property string $salt
 * @property string $access_token
 * @property string $auth_key
 * @property integer $company_id
 * @property integer $status
 * @property string $forgot_hash
 * @property string $google_refresh_token
 * @property string $device_key
 *
 * @property Company $company
 * @property Staff $staff
 * @property UserDivision[] $divisions
 * @property ConfirmKey[] $confirmKeys
 */
class User extends ActiveRecord implements IdentityInterface
{
    public $role;
    public $code;
    public $password;

    private $_permitted_divisions = null;

    /**
     * Status
     */
    const STATUS_DISABLED = 0;
    const STATUS_ENABLED = 1;

    const SESSION_TIMEOUT = 365 * 24 * 60 * 60;

    /**
     * @param bool $insert
     * @param array $changedAttributes
     */
    public function afterSave($insert, $changedAttributes)
    {
        $isCompanyChanged = isset($changedAttributes['company_id']);
        $oldCompanyId = $changedAttributes['company_id'] ?? null;
        $roles = ArrayHelper::getColumn($this->getRoles(), 'name');
        $isAdmin = in_array(AuthItem::ROLE_ADMINISTRATOR, $roles);

        if ($insert || ($isCompanyChanged && $oldCompanyId != $this->company_id && !$isAdmin)) {
            if (!$this->password) {
                $this->generatePassword();
            }
            $this->sendPassword();
        }

        parent::afterSave($insert, $changedAttributes);
    }

    /**
     * @param $company_id
     * @param $password
     * @param $role
     * @param $username
     *
     * @return User
     * @throws \yii\base\Exception
     */
    public static function add($company_id, $username, $password, $role)
    {
        $user = new User();
        $user->company_id = $company_id;
        $user->password = $password;
        $user->role = $role;
        $user->username = $username;
        $user->salt = Yii::$app->security->generateRandomString();
        if ($password == null) {
            $user->generatePassword();
        }
        $user->generatePasswordHash();
        $user->generateAuthKey();
        $user->generateToken();
        $user->status = User::STATUS_ENABLED;
        return $user;
    }

    /**
     * @param $company_id
     * @param $password
     * @param $role
     * @param $status
     * @param $username
     *
     * @throws \yii\base\Exception
     */
    public function edit($company_id, $password, $role, $status, $username)
    {
        $this->company_id = $company_id;
        $this->password = $password;
        $this->role = $role;
        $this->status = $status;
        $this->username = $username;
        if ($password != null) {
            $this->editPassword($password);
        }
        $this->generateAuthKey();
        $this->generateToken();
    }

    /**
     * @param $newPassword
     *
     * @throws \yii\base\Exception
     */
    public function editPassword($newPassword)
    {
        $this->password = $newPassword;
        $this->generatePasswordHash();
    }

    /**
     * @param integer $status
     */
    public function setStatus(int $status)
    {
        if ( ! isset(UserHelper::getStatuses()[$status])) {
            throw new \DomainException('Wrong status');
        }

        $this->status = $status;
    }

    /**
     * @throws \yii\base\Exception
     */
    public function generatePasswordHash()
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($this->password . $this->salt);
    }

    /**
     * Sends message with password
     */
    public function sendPassword()
    {
        if (YII_ENV_TEST) {
            return;
        }

        $message = Yii::t('app',
            'Dear %s. Thank you for choosing MYCRM. Your password: %s. You can log in via www.mycrm.kz.');
        $message = sprintf($message, $this->getFullName(), $this->password);
        CustomerRequest::sendNotAssignedSMS($this->username, $message, $this->company_id);
    }

    /**
     * @param $accesses
     */
    public function setAccesses($accesses)
    {
        AuthAssignment::setAccesses($accesses, $this);
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%users}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['username', 'company_id'], 'required'],
            [['company_id'], 'integer'],
            [[
                'access_token', 'auth_key', 'forgot_hash', 'google_refresh_token',
                'key_confirm', 'password_hash', 'role', 'username'
            ], 'string', 'max' => 255
            ],
            ['username', 'match', 'pattern' => CustomerHelper::PHONE_VALIDATE_PATTERN],
            ['status', 'boolean'],
            ['status', 'default', 'value' => self::STATUS_ENABLED],
            [['username'], 'unique'],
            ['company_id', 'exist', 'targetClass' => Company::className(), 'targetAttribute' => ['company_id' => 'id']],
            ['device_key', 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'            => Yii::t('app', 'ID'),
            'username'      => Yii::t('app', 'Username'),
            'password_hash' => Yii::t('app', 'Password'),
            'salt'          => Yii::t('app', 'Salt'),
            'company_id'    => Yii::t('app', 'Company ID'),
            'access_token'  => Yii::t('app', 'Access Token'),
            'auth_key'      => Yii::t('app', 'Auth Key'),
            'status'        => Yii::t('app', 'Status'),
        ];
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            HistoryBehavior::className(),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCompany()
    {
        return $this->hasOne(Company::className(), ['id' => 'company_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStaff()
    {
        return $this->hasOne(Staff::className(), ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDivisions()
    {
        return $this->hasMany(UserDivision::className(), ['staff_id' => 'id'])->via('staff');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getConfirmKey()
    {
        return $this->hasMany(ConfirmKey::className(), ['username' => 'username']);
    }

    /**
     * @inheritdoc
     */
    public static function find()
    {
        return new UserQuery(get_called_class());
    }

    /**
     * Password validation.
     * @param string $password
     * @return boolean
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password . $this->salt, $this->password_hash);
    }

    /**
     * Find model by username.
     * @param string $username Username
     * @return User
     */
    public static function findByUsername($username)
    {
        return User::find()->where(['username' => $username])->enabled()->one();
    }

    /**
     * Returns whether token is set
     * @return bool
     */
    public function hasValidToken(): bool
    {
        return $this->access_token !== null && Security::isValidToken($this->access_token, User::SESSION_TIMEOUT);
    }

    /**
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
        return static::findOne($id);
    }

    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        if ($user = static::findOne(['access_token' => $token])) {
            return $user->isAccessTokenValid() ? $user : null;
        }
        return null;
    }

    /**
     * Checks if user has available schedule(if company set auth limit time)
     * Otherwise returns true
     * @return bool
     */
    public function isAccessTokenValid()
    {
        if ( ! empty($this->access_token)) {
            if($this->isTimeLimitedBySchedule()){
                return $this->staff->getScheduleForNow() ? true : false;
            }
        }
        return true;
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
        return $this->access_token;
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($access_token)
    {
        return $this->access_token === $access_token && Security::isValidToken($this->access_token, User::SESSION_TIMEOUT);
    }

    /**
     * Generates "remember me" authentication key.
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    /**
     * Generates secure key.
     */
    public function generateToken()
    {
        $this->access_token = Security::generateExpiringRandomString();
    }

    /**
     * @return string
     */
    public function getValidAccessToken()
    {
        if ( ! $this->hasValidToken()) {
            $this->generateToken();
        }

        return $this->access_token;
    }

    /**
     * Generates password.
     */
    private function generatePassword()
    {
        $this->password = Security::random_str(6, "abcdefghijklmnopqrstuvwxyz");
    }

    /**
     * Disable user
     * @return boolean
     */
    public function disable()
    {
        $this->status = User::STATUS_DISABLED;
        return $this->save();
    }

    /**
     * Enable user
     */
    public function enable()
    {
        $this->status = User::STATUS_ENABLED;
    }

    /**
     * @return int
     */
    public function isDisabled()
    {
        return $this->status == User::STATUS_DISABLED;
    }

    /**
     * @return array
     */
    public function getPermittedDivisions()
    {
        if ($this->_permitted_divisions === null) {
            $userDivisions              = $this->divisions;
            $this->_permitted_divisions = empty($userDivisions)
                ? $this->company->getDivisions()->select('id')->column()
                : ArrayHelper::getColumn($userDivisions, 'division_id');
        }

        return $this->_permitted_divisions;
    }

    /**
     * Gets name of user
     * @return string
     */
    public function getName() {
        if ($this->staff) {
            return $this->staff->name;
        }
        return Yii::t('app', 'Administrator');
    }

    /**
     * Gets full name of user
     * @return string
     */
    public function getFullName() {
        if ($this->staff) {
            return $this->staff->fullName;
        }
        return $this->company->ceoName;
    }

    /**
     * @return \yii\rbac\Permission[]
     */
    public function getPermissions()
    {
        return \Yii::$app->authManager->getPermissionsByUser($this->id);
    }

    /**
     * @return \yii\rbac\Role[]
     */
    public function getRoles()
    {
        return \Yii::$app->authManager->getRolesByUser($this->id);
    }

    /**
     * @return array
     */
    public function fields()
    {
        return [
            'id'                   => 'id',
            'username'             => 'username',
            'company_id'           => 'company_id',
            'status'               => 'status',
            'google_refresh_token' => 'google_refresh_token',
            'can_update_order'     => function() {
                return Yii::$app->user->can("administrator") || ($this->staff && $this->staff->can_update_order);
            }
        ];
    }

    public function canSeeCustomerPhones()
    {
        return !$this->staff || $this->staff->canSeeCustomerPhones();
    }

    /**
     * Checks if user's company set auth limit time by work hours
     * @return bool
     */
    public function isTimeLimitedBySchedule()
    {
        return isset($this->staff) && isset($this->company) && $this->company->limit_auth_time_by_schedule;
    }
}
