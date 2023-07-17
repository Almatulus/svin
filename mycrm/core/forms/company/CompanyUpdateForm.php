<?php

namespace core\forms\company;

use core\models\company\Company;
use Yii;
use yii\base\Model;

/**
 * @property string $name
 * @property string $head_name
 * @property string $head_surname
 * @property string $head_patronymic
 * @property string $widget_prefix;
 * @property string $iik;
 * @property string $bik;
 * @property string $bin;
 * @property string $bank;
 * @property string $license_issued;
 * @property string $license_number;
 * @property string $address;
 * @property string $phone;
 * @property string $online_start;
 * @property string $online_finish;
 * @property integer $logo_id;
 * @property integer $cashback_percent
 * @property Company $company
 */
class CompanyUpdateForm extends Model
{
    public $name;
    public $head_name;
    public $head_surname;
    public $head_patronymic;
    public $widget_prefix;
    public $iik;
    public $bik;
    public $bin;
    public $bank;
    public $license_issued;
    public $license_number;
    public $address;
    public $phone;
    public $online_start;
    public $online_finish;
    public $image_file;
    public $logo_id;
    public $notify_about_order;
    public $cashback_percent;

    public $company;

    public function __construct(Company $company, $config = [])
    {
        $this->company = $company;
        $this->name = $company->name;
        $this->head_name = $company->head_name;
        $this->head_surname = $company->head_surname;
        $this->head_patronymic = $company->head_patronymic;
        $this->widget_prefix = $company->widget_prefix ?: $company->id;
        $this->address = $company->address;
        $this->bank = $company->bank;
        $this->bik = $company->bik;
        $this->bin = $company->bin;
        $this->iik = $company->iik;
        $this->license_issued = $company->license_issued;
        $this->license_number = $company->license_number;
        $this->phone = $company->phone;
        $this->online_start = $company->online_start;
        $this->online_finish = $company->online_finish;
        $this->logo_id = $company->logo_id;
        $this->notify_about_order = $company->notify_about_order;
        $this->cashback_percent = $company->cashback_percent;
        parent::__construct($config);
    }

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['name', 'head_name', 'widget_prefix'], 'required'],

            [
                ['address', 'phone', 'bank', 'name', 'head_name', 'head_surname', 'head_patronymic', 'license_number'],
                'string',
                'max' => 255
            ],

            ['widget_prefix', 'match', 'pattern' => '/^[0-9a-z]+$/'],
            ['widget_prefix', 'string', 'min' => 1, 'max' => 32],
            ['widget_prefix', 'filter', 'filter' => 'trim', 'skipOnEmpty' => true],

            [
                ['widget_prefix'],
                'unique',
                'targetClass' => Company::className(),
                'filter'      => ['<>', 'id', $this->company->id]
            ],

//            ['iik', 'match', 'pattern' => '/^[0-9]{20}$/'],
//            ['bin', 'match', 'pattern' => '/^[0-9]{12}$/'],
//            ['bik', 'match', 'pattern' => '/^[0-9]{8}$/'],
//
//            ['iik', 'string', 'min' => 20, 'max' => 20],
//            ['bin', 'string', 'min' => 12, 'max' => 12],
//            ['bik', 'string', 'min' => 8, 'max' => 8],
            [['iik', 'bin', 'bik', 'imageFile'], 'safe'],

            [['license_issued'], 'date', 'format' => 'php:Y-m-d'],
            [['online_start', 'online_finish'], 'date', 'format' => 'php:H:i'],
            ['online_finish', 'compare', 'compareAttribute' => 'online_start', 'operator' => '>'],

            ['notify_about_order', 'boolean'],

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
            'head_name'          => Yii::t('app', 'Head Name'),
            'head_surname'       => Yii::t('app', 'Head Surname'),
            'head_patronymic'    => Yii::t('app', 'Head Patronymic'),
            'iik'                => Yii::t('app', 'IIK'),
            'bik'                => Yii::t('app', 'BIK'),
            'bin'                => Yii::t('app', 'BIN'),
            'bank'               => Yii::t('app', 'Bank'),
            'license_issued'     => Yii::t('app', 'License issued'),
            'license_number'     => Yii::t('app', 'License number'),
            'address'            => Yii::t('app', 'Address'),
            'phone'              => Yii::t('app', 'Phone'),
            'widget_prefix'      => Yii::t('app', 'Widget Prefix'),
            'online_start'       => Yii::t('app', 'Online start'),
            'online_finish'      => Yii::t('app', 'Online finish'),
            'image_file'         => Yii::t('app', 'Image File'),
            'notify_about_order' => Yii::t('app', 'Receive web notifications about order creation.'),
            'cashback_percent'   => Yii::t('app', 'Cashback Percent'),
        ];
    }

    public function formName()
    {
        return 'Company';
    }
}
