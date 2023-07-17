<?php

namespace core\forms\staff;

use core\helpers\customer\CustomerHelper;
use core\helpers\StaffHelper;
use core\models\division\Division;
use core\models\query\StaffQuery;
use core\models\Staff;
use core\models\user\User;
use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

/**
 * This is the model class for table "{{%staffs}}".
 *
 * @property string  $name
 * @property string  $phone
 * @property integer $has_calendar
 * @property string  $color
 * @property array   $division_ids
 * @property array   $user_permissions
 * @property array   $user_divisions
 * @property array   $division_service_ids
 * @property boolean $see_own_orders
 * @property boolean $can_create_order
 * @property boolean $can_update_order
 * @property boolean $see_customer_phones
 * @property integer $gender
 * @property string  $birth_date
 * @property array   $company_position_ids
 * @property string  $description
 * @property string  $description_private
 * @property string  $image_file
 * @property string  $surname
 * @property array   $create_user
 * @property string  $username
 * @property string $code_1c
 */
class StaffUpdateForm extends Model
{
    public $name;
    public $surname;
    public $company_position_ids = [];
    public $phone;
    public $description;
    public $gender;
    public $description_private;
    public $has_calendar;
    public $color;
    public $see_own_orders;
    public $can_create_order;
    public $can_update_order;
    public $image_file;
    public $division_service_ids;
    public $division_ids;
    public $user_divisions;
    public $user_permissions = [];
    public $create_user = false;
    public $birth_date;
    public $username;
    public $see_customer_phones;
    public $code_1c;

    private $_staff;

    public function __construct(Staff $staff, array $config = [])
    {
        $this->_staff              = $staff;
        $this->name                = $staff->name;
        $this->surname             = $staff->surname;
        $this->phone               = $staff->phone;
        $this->description         = $staff->description;
        $this->gender              = $staff->gender;
        $this->description_private = $staff->description_private;
        $this->has_calendar        = $staff->has_calendar;
        $this->color               = $staff->color;
        $this->see_own_orders      = $staff->see_own_orders;
        $this->can_create_order    = $staff->create_order;
        $this->can_update_order    = $staff->can_update_order;
        $this->birth_date          = $staff->birth_date;
        $this->see_customer_phones = $staff->see_customer_phones;
        $this->code_1c             = $staff->code_1c;

        $this->company_position_ids = ArrayHelper::getColumn(
            $staff->companyPositions,
            'id'
        );

        $this->division_service_ids = ArrayHelper::getColumn(
            $staff->divisionServices,
            'id'
        );

        $this->division_ids = ArrayHelper::getColumn(
            $staff->divisions,
            'id'
        );

        if ($staff->hasUserPermissions()) {
            $this->username         = $staff->user->username;
            $this->create_user      = true;
            $this->user_divisions   = ArrayHelper::getColumn(
                $staff->userDivisions,
                'division_id'
            );
            $permissions = Yii::$app->authManager->getPermissionsByUser($staff->user_id);
            $this->user_permissions = ArrayHelper::getColumn($permissions, 'name');
        } else {
            $this->user_divisions
                = Division::find()->company()->enabled()->column();
        }

        parent::__construct($config);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'color', 'division_ids'], 'required'],
            [['gender', 'has_calendar'], 'integer'],
            [['description', 'description_private'], 'string'],
            ['surname', 'default', 'value' => null],
            [['name', 'surname', 'phone', 'color', 'username'], 'string', 'max' => 255],
            [['birth_date'], 'date', 'format' => 'php:Y-m-d'],

            [
                'name',
                'filter',
                'filter' => function ($name) {
                    return ucwords(trim($name));
                }
            ],

            [
                'division_service_ids',
                'filter',
                'filter' => function ($value) {
                    $division_service_ids = is_array($value) ? $value : json_decode($value);

                    return is_array($division_service_ids)
                        ? array_unique($division_service_ids) : [];
                }
            ],

            [
                'user_permissions',
                'filter',
                'filter' => function ($value) {
                    $value             = is_array($value) ? $value : Json::decode($value);
                    $staff_permissions = StaffHelper::getPermissionsList();

                    $value = array_filter(
                        $value,
                        function ($permission) use ($staff_permissions) {
                            return in_array($permission, $staff_permissions);
                        }
                    );

                    return is_array($value) ? array_unique($value) : [];
                }
            ],

            ['division_service_ids', 'each', 'rule' => ['integer']],

            [
                'username',
                'required',
                'enableClientValidation' => false,
                'when' => function () {
                    return $this->create_user;
                }
            ],
            [
                'username',
                'validateUsername',
            ],

            [['user_divisions'], 'safe'],
            ['phone', 'default', 'value' => null],
            [
                ['phone', 'username'],
                'match',
                'pattern' => CustomerHelper::PHONE_VALIDATE_PATTERN,
                'skipOnEmpty' => true
            ],

            [
                ['image_file'],
                'file',
                'skipOnEmpty' => true,
                'extensions'  => 'png, jpg, jpeg'
            ],
            [['can_update_order', 'create_user', 'see_own_orders', 'see_customer_phones'], 'boolean'],
            [
                ['can_update_order', "see_own_orders", 'can_create_order'],
                'default',
                'value' => false
            ],

            ['company_position_ids', 'default', 'value' => []],
            ['division_ids', 'each', 'rule' => ['integer']],
            ['company_position_ids', 'each', 'rule' => ['integer']],

            ['code_1c', 'default', 'value' => null],
            ['code_1c', 'string', 'max' => '255'],
            [
                'code_1c',
                'unique',
                'targetClass' => Staff::class,
                'filter'      => function (StaffQuery $query) {
                    return $query->company(false)->andWhere(['<>', 'id', $this->_staff->id]);
                }
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'name'                => Yii::t('app', 'Staff Name'),
            'surname'             => Yii::t('app', 'Staff Surname'),
            'phone'               => Yii::t('app', 'Contact phone'),
            'username'            => Yii::t('app', 'Auth phone'),
            'description'         => Yii::t('app', 'Description'),
            'description_private' => Yii::t('app', 'Description Private'),
            'services'            => Yii::t('app', 'Services'),
            'company_position_ids' => Yii::t('app', 'Company Position IDs'),
            'has_calendar'        => Yii::t('app', 'Staff has calendar'),
            'color'               => Yii::t('app', 'Staff color'),
            'gender'              => Yii::t('app', 'Gender'),
            'user_permissions'    => Yii::t('app', 'Staff Permissions'),
            'user_divisions'      => Yii::t('app', 'Staff Divisions'),
            'create_user'         => Yii::t('app', 'Give access to system'),
            'see_own_orders'      => Yii::t('app', 'See only own orders'),
            'can_create_order'    => Yii::t('app', 'Create order permission'),
            'can_update_order'          => Yii::t('app', 'Может редактировать прошлые записи'),
            'division_ids'        => Yii::t('app', 'Division ID'),
            'birth_date'          => Yii::t('app', 'Birth Date'),
            'see_customer_phones' => Yii::t('app', 'See customer phones'),
            'code_1c'              => Yii::t('app', '1C Nomenclature code')
        ];
    }

    public function formName()
    {
        return 'Staff';
    }

    /**
     * Checks `username` uniqueness by staff's `user_id`
     * @param $attribute
     * @param $params
     */
    public function validateUsername($attribute, $params)
    {
        $user = User::find()->where(['username' => $this->$attribute])->one();
        if ($user && $this->_staff->user_id !== $user->id) {
            $staff = Staff::find()->where(['user_id' => $user->id])->one();
            if ($staff) {
                $this->addError($attribute, Yii::t('app', 'This number already exists'));
            }
        }
    }
}