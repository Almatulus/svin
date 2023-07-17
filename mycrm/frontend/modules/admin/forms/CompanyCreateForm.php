<?php

namespace frontend\modules\admin\forms;

use core\helpers\CompanyHelper;
use core\models\company\Company;
use core\models\company\Tariff;
use core\models\ServiceCategory;
use Yii;
use yii\base\Model;

/**
 * @property string $name
 * @property string $head_name
 * @property string $head_surname
 * @property string $head_patronymic
 * @property boolean $publish
 * @property boolean $enable_web_call
 * @property integer $status
 * @property integer $tariff_id
 * @property integer $category_id
 * @property boolean $file_manager_enabled
 * @property boolean $show_referrer
 * @property boolean $show_new_interface
 * @property boolean $unlimited_sms
 * @property boolean $notify_about_order
 * @property integer $cashback_percent
 * @property boolean $limit_auth_time_by_schedule
 */
class CompanyCreateForm extends Model
{
    public $name;
    public $head_name;
    public $head_surname;
    public $head_patronymic;
    public $publish;
    public $status;
    public $enable_web_call;
    public $tariff_id;
    public $category_id;
    public $file_manager_enabled;
    public $show_referrer = false;
    public $show_new_interface = false;
    public $interval = 5;
    public $unlimited_sms;
    public $notify_about_order;
    public $cashback_percent;
    public $limit_auth_time_by_schedule = false;
    public $enable_integration;

    public $iik;
    public $bik;
    public $bin;
    public $bank;
    public $license_issued;
    public $license_number;
    public $address;
    public $phone;

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [
                [
                    'name',
                    'head_name',
                    'status',
                    'category_id',
                    'publish',
                    'enable_web_call',
                    'tariff_id'
                ],
                'required'
            ],

            ['enable_integration', 'default', 'value' => false],
            [
                [
                    'enable_web_call',
                    'enable_integration',
                    'file_manager_enabled',
                    'publish',
                    'show_referrer',
                    'show_new_interface',
                    'unlimited_sms',
                    'notify_about_order',
                    'limit_auth_time_by_schedule'
                ],
                'boolean'
            ],

            [['license_issued'], 'date', 'format' => 'php:Y-m-d'],

            [['address', 'phone', 'bank', 'name', 'head_name', 'head_surname', 'head_patronymic', 'license_number'], 'string', 'max' => 255],
            [['name'], 'unique', 'targetClass' => Company::className(), 'targetAttribute' => 'name'],

//            ['iik', 'match', 'pattern' => '/^[0-9]{20}$/'],
//            ['bin', 'match', 'pattern' => '/^[0-9]{12}$/'],
//            ['bik', 'match', 'pattern' => '/^[0-9]{8}$/'],
//            ['iik', 'string', 'min' => 20, 'max' => 20],
//            ['bin', 'string', 'min' => 12, 'max' => 12],
//            ['bik', 'string', 'min' => 8, 'max' => 8],
            [['iik', 'bin', 'bik'], 'safe'],

            [['status', 'category_id', 'tariff_id'], 'integer'],
            [['interval'], 'integer', 'min' => 0],
            ['status', 'in', 'range' => array_keys(CompanyHelper::getStatuses())],
            [['category_id'], 'exist', 'skipOnError' => false,
                'targetClass' => ServiceCategory::className(), 'targetAttribute' => ['category_id' => 'id']],
            [
                ['tariff_id'],
                'exist',
                'skipOnError'     => true,
                'targetClass'     => Tariff::className(),
                'targetAttribute' => ['tariff_id' => 'id']
            ],

            ['cashback_percent', 'default', 'value' => null],
            ['cashback_percent', 'integer', 'min' => 0, 'max' => 100]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'name'               => Yii::t('app', 'Name'),
            'status'             => Yii::t('app', 'Status'),
            'head_name'          => Yii::t('app', 'Head Name'),
            'head_surname'       => Yii::t('app', 'Head Surname'),
            'head_patronymic'    => Yii::t('app', 'Head Patronymic'),
            'category_id'        => Yii::t('app', 'Category ID'),
            'publish'              => Yii::t('app', 'Publish'),
            'tariff_id'            => Yii::t('app', 'Tariff'),
            'enable_web_call'      => Yii::t('app', 'Web Call'),
            'file_manager_enabled' => Yii::t('app', 'Enable file manager'),
            'show_referrer'        => Yii::t('app', 'Show referrer'),
            'show_new_interface'   => Yii::t('app', 'Show new interface'),

            'iik'            => Yii::t('app', 'IIK'),
            'bik'                => Yii::t('app', 'BIK'),
            'bin'                => Yii::t('app', 'BIN'),
            'bank'               => Yii::t('app', 'Bank'),
            'license_issued'     => Yii::t('app', 'License issued'),
            'license_number'     => Yii::t('app', 'License number'),
            'address'            => Yii::t('app', 'Address'),
            'phone'              => Yii::t('app', 'Phone'),
            'interval'           => Yii::t('app', 'Interval'),
            'unlimited_sms'      => Yii::t('app', 'Unlimited SMS'),
            'notify_about_order' => Yii::t('app', 'Receive web notifications about order creation.'),
            'cashback_percent'   => Yii::t('app', 'Cashback Percent'),
            'limit_auth_time_by_schedule' => Yii::t('app','Limit auth time by schedule'),
        ];
    }

    public function formName()
    {
        return 'Company';
    }
}
