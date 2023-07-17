<?php

namespace core\models\division;

use common\components\HistoryBehavior;
use core\helpers\division\ServiceHelper;
use core\models\division\query\DivisionServiceQuery;
use core\models\InsuranceCompany;
use core\models\order\Order;
use core\models\order\OrderService;
use core\models\ServiceCategory;
use core\models\Staff;
use Yii;
use yii\helpers\ArrayHelper;
use yii\validators\CompareValidator;

/**
 * This is the model class for table "{{%division_services}}".
 *
 * @property integer $id
 * @property integer $price
 * @property integer $price_max
 * @property integer $average_time
 * @property string $service_name
 * @property string $description
 * @property integer $status
 * @property boolean $publish
 * @property boolean $insurance_company_id
 * @property boolean $is_trial
 * @property integer $notification_delay
 * @property string $code_1c
 *
 * @property ServiceCategory[] $categories
 * @property Division[] $divisions
 * @property DivisionServiceProduct[] products
 * @property Order[] $orders
 * @property Staff[] $staffs
 * @property InsuranceCompany $insuranceCompany
 * @property DivisionServiceInsuranceCompany[] $insuranceCompanies
 */
class DivisionService extends \yii\db\ActiveRecord
{
    /**
     * Statuses
     */
    const STATUS_DELETED = 0;
    const STATUS_ENABLED = 1;

    public $service_ids = [];

    /**
     * @param $average_time
     * @param $publish
     * @param $is_trial
     * @param $description
     * @param $price
     * @param $price_max
     */
    public function edit(
        $average_time,
        $publish,
        $is_trial,
        $description,
        $price,
        $price_max
    ) {
        $this->average_time = $average_time;
        $this->publish      = $publish;
        $this->is_trial     = $is_trial;
        $this->description  = $description;
        $this->price        = $price;
        $this->price_max    = $price_max;
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%division_services}}';
    }

    /**
     *
     */
    public function setDeleted()
    {
        $this->status = DivisionService::STATUS_DELETED;
    }

    /**
     * Enables service
     */
    public function restore()
    {
        $this->status = self::STATUS_ENABLED;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['price', 'average_time', 'service_name'], 'required'],
            [['price_max', 'price', 'average_time'], 'integer'],
            [
                'price_max',
                'compare',
                'compareAttribute' => 'price',
                'operator'         => '>',
                'type'             => CompareValidator::TYPE_NUMBER
            ],

            [['description'], 'string'],

            [['is_trial'], 'boolean'],

            ['insurance_company_id', 'integer'],
            ['insurance_company_id', 'exist', 'targetClass' => InsuranceCompany::class, 'targetAttribute' => 'id'],

            ['notification_delay', 'integer'],
            ['notification_delay', 'in', 'range' => array_keys(ServiceHelper::all())],

            ['publish', 'default', 'value' => true],
            ['publish', 'boolean'],

            ['service_name', 'string', 'max' => 255],

            ['code_1c', 'default', 'value' => null],
            ['code_1c', 'string', 'max' => '255'],
            [
                'code_1c',
                'unique',
                'targetClass' => DivisionService::class,
                'filter'      => function (DivisionServiceQuery $query) {
                    return $query->company(null, false)->andFilterWhere(['<>', '{{%division_services}}.id', $this->id]);
                }
            ],
//            ['service_name', 'unique', 'targetAttribute' => ['service_name', 'division_id'], 'message' => 'Услуга с таким наименованием для данного заведения уже существует'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'                   => Yii::t('app', 'ID'),
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
            'divisions'            => Yii::t('app', 'Divisions'),
            'code_1c'              => Yii::t('app', '1C Nomenclature code')
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDivisions()
    {
        return $this->hasMany(Division::className(), ['id' => 'division_id'])
            ->viaTable('{{%service_division_map}}', ['division_service_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProducts()
    {
        return $this->hasMany(DivisionServiceProduct::className(), ['division_service_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getInsuranceCompanies()
    {
        return $this->hasMany(DivisionServiceInsuranceCompany::className(), ['division_service_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrders()
    {
        return $this->hasMany(Order::className(), ['id' => 'order_id'])->via('orderServices');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrderServices()
    {
        return $this->hasMany(OrderService::className(), ['division_service_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStaffs()
    {
        return $this->hasMany(Staff::className(), ['id' => 'staff_id'])
            ->viaTable('{{%staff_division_service_map}}', ['division_service_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCategories()
    {
        return $this->hasMany(ServiceCategory::className(), ['id' => 'category_id'])
            ->viaTable('crm_division_services_map', ['division_service_id' => 'id'])
            ->onCondition(['{{%service_categories}}.status' => ServiceCategory::STATUS_ENABLED]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getInsuranceCompany()
    {
        return $this->hasOne(InsuranceCompany::className(), ['id' => 'insurance_company_id']);
    }

    /**
     * Returns list division services in own company
     */
    public static function getOwnCompanyDivisionServicesList()
    {
        $models = DivisionService::find()
            ->deleted(false)
            ->company()
            ->joinWith(['categories'])
            ->all();

        $result = [];
        foreach ($models as $divisionService) {
            /* @var DivisionService $divisionService */
            $categories = $divisionService->categories;
            if (empty($categories)) {
                $result[$divisionService->id] = $divisionService->service_name;
            } else {
                foreach ($categories as $category) {
                    $result[$category->name][$divisionService->id] = $divisionService->service_name;
                }
            }
        }

        return $result;
    }

    public static function getCompanyTreeStructure(Array $selectedValues = null)
    {
        $models = DivisionService::find()
            ->company(null, false)
            ->joinWith(['categories'], false)
            ->select([
                'crm_division_services.id as key',
                'service_name as title',
                '{{%service_categories}}.id as cat_id',
                'division_id'
            ])
            ->orderBy('{{%service_categories}}.name ASC, {{%division_services}}.service_name ASC')
            ->asArray()
            ->all();

        $result        = [];
        $models        = ArrayHelper::index($models, null, 'cat_id');
        $categoryNames = ArrayHelper::map(
            ServiceCategory::getCompanyCategories(),
            'id',
            'name'
        );
        foreach ($models as $key => $services) {
            if ( ! empty($selectedValues)) {
                foreach ($services as $k => $service) {
                    $selected = false;
                    if (in_array($service['key'], $selectedValues)) {
                        $selected = true;
                    }
                    $services[$k]['selected'] = $selected;
                }
            }

            if ($key && isset($categoryNames[$key])) {
                $result[] = [
                    'title'    => '<b>' . $categoryNames[$key] . '</b>',
                    'children' => $services,
                    "expanded" => true,
                    'folder'   => true,
                    'selected' => false
                ];
            } else {
                array_push($result, $services[0]);
            }
        }

        return $result;
    }

    /**
     * @return string
     */
    public function getFullName() {
        return $this->service_name . ' (' . $this->average_time . ' мин, ' . $this->price . '〒)';
    }

    /**
     * @inheritdoc
     */
    public static function find()
    {
        return new DivisionServiceQuery(get_called_class());
    }

    /**
     * @return array
     */
    public function extraFields()
    {
        return [
            'divisions',
            'products',
            'insurance-companies' => function(){
                return $this->insuranceCompanies;
            }
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            HistoryBehavior::className(),
        ];
    }

    /**
     * @return null|string
     */
    public function getDelay()
    {
        return $this->notification_delay ? ServiceHelper::getIntervals()[$this->notification_delay] : null;
    }

    /**
     * @param string $separator
     * @return string
     */
    public function getCategoriesTitle($separator = "<br>")
    {
        return implode($separator, ArrayHelper::getColumn($this->categories, "name"));
    }
}
