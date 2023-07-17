<?php

namespace core\forms\division;

use core\helpers\division\ServiceHelper;
use core\models\division\Division;
use core\models\division\DivisionService;
use core\models\division\query\DivisionServiceQuery;
use core\models\InsuranceCompany;
use core\models\ServiceCategory;
use core\models\Staff;
use Yii;
use yii\base\Model;
use yii\validators\CompareValidator;

/**
 * Class ServiceCreateForm
 * @package core\forms\division
 */
class ServiceCreateForm extends Model
{
    public $average_time;
    public $description;
    public $division_ids;
    public $insurance_company_id;
    public $is_trial;
    public $notification_delay;
    public $price;
    public $price_max;
    public $publish = true;
    public $service_name;
    public $code_1c;

    public $category_ids;
    public $staff;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            ['average_time', 'required'],
            ['average_time', 'integer', 'min' => 0],

            ['category_ids', 'default', 'value' => []],
            ['category_ids', 'each', 'rule' => ['integer']],
            [
                'category_ids',
                'each',
                'rule' => ['exist', 'targetClass' => ServiceCategory::class, 'targetAttribute' => 'id']
            ],

            ['description', 'string'],

            ['division_ids', 'required'],
            ['division_ids', 'each', 'rule' => ['integer']],
            ['division_ids', 'each', 'rule' => ['exist', 'targetClass' => Division::class, 'targetAttribute' => 'id']],

            ['insurance_company_id', 'integer'],
            ['insurance_company_id', 'exist', 'targetClass' => InsuranceCompany::class, 'targetAttribute' => 'id'],

            ['is_trial', 'default', 'value' => false],
            ['is_trial', 'boolean'],

            ['notification_delay', 'integer'],
            ['notification_delay', 'in', 'range' => array_keys(ServiceHelper::all())],

            ['price', 'required'],
            ['price', 'integer', 'min' => 0],

            ['price_max', 'integer', 'min' => 0],
            [
                'price_max',
                'compare',
                'compareAttribute'       => 'price',
                'operator'               => '>',
                'type'                   => CompareValidator::TYPE_NUMBER,
                'enableClientValidation' => false
            ],

            ['publish', 'default', 'value' => true],
            ['publish', 'boolean'],

            ['service_name', 'required'],
            ['service_name', 'string', 'max' => 255],

            ['staff', 'default', 'value' => []],
            ['staff', 'each', 'rule' => ['integer']],
            ['staff', 'each', 'rule' => ['exist', 'targetClass' => Staff::class, 'targetAttribute' => 'id']],

            ['code_1c', 'default', 'value' => null],
            ['code_1c', 'string', 'max' => '255'],
            [
                'code_1c',
                'unique',
                'targetClass' => DivisionService::class,
                'filter'      => function (DivisionServiceQuery $query) {
                    $query->company(null, false);
                    if (isset($this->service)) {
                        return $query->andWhere(['<>', 'id', $this->service->id]);
                    }
                    return $query;
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
            'division_ids'         => Yii::t('app', 'Divisions'),
            'price'                => Yii::t('app', 'Price currency'),
            'price_max'            => Yii::t('app', 'Max Price currency'),
            'average_time'         => Yii::t('app', 'Duration'),
            'service_name'         => Yii::t('app', 'Service Name'),
            'description'          => Yii::t('app', 'Description'),
            'service_ids'          => Yii::t('app', 'Search Category'),
            'publish'              => Yii::t('app', 'Available for online booking'),
            'staff'                => Yii::t('app', 'Service Staff'),
            'is_trial'             => Yii::t('app', 'Trial service'),
            'insurance_company_id' => Yii::t('app', 'Insurance Company'),
            'notification_delay'   => Yii::t('app', 'Notification delay'),
            'code_1c'              => Yii::t('app', '1C Nomenclature code')
        ];
    }
}