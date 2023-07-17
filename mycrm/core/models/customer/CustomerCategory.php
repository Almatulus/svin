<?php

namespace core\models\customer;

use core\models\company\Company;
use core\models\customer\query\CustomerCategoryQuery;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "crm_customer_categories".
 *
 * @property integer $id
 * @property string $name
 * @property string $color
 * @property string $company_id
 * @property integer $discount
 * @property integer $cashback_percent
 *
 * @property Customer[] $customers
 * @property CustomerLoyalty[] $customerLoyalties
 * @property Company $company
 */
class CustomerCategory extends \yii\db\ActiveRecord
{
    /**
     * @param string $name
     * @param int $company_id
     * @param $discount
     * @param $color
     * @param int|null $cashback_percent
     * @return CustomerCategory
     */
    public static function add(
        string $name,
        int $company_id,
        $discount,
        $color,
        int $cashback_percent = null
    ): CustomerCategory
    {
        $model = new self();
        $model->name = $name;
        $model->company_id = $company_id;
        $model->discount = $discount;
        $model->color = $color ?? "#888888";
        $model->cashback_percent = $cashback_percent;

        return $model;
    }

    /**
     * @param string $name
     * @param $discount
     * @param $color
     * @param int|null $cashback_percent
     * @return CustomerCategory
     */
    public function edit(string $name, $discount, $color, int $cashback_percent = null): CustomerCategory
    {
        $this->name = $name;
        $this->discount = $discount;
        $this->color = $color;
        $this->cashback_percent = $cashback_percent;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%customer_categories}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'company_id'], 'required'],
            [['discount', 'cashback_percent'], 'integer'],
            [['name'], 'string', 'max' => 255],
            [['color'], 'string', 'max' => 7],
            ['name', 'filter', 'filter' => 'trim'],
            ['name', 'unique', 'targetAttribute' => ['company_id', 'name'],
                'message' => Yii::t('yii', '{attribute} "{value}" has already been taken.')
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'               => 'ID',
            'name'             => Yii::t('app', 'Name'),
            'color'            => Yii::t('app', 'Color'),
            'discount'         => Yii::t('app', 'Discount'),
            'cashback_percent' => Yii::t('app', "Cashback Percent"),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCustomers()
    {
        return $this->hasMany(Customer::className(), ['id' => 'customer_id'])
            ->viaTable('crm_company_customer_category_map', ['category_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCustomerLoyalties()
    {
        return $this->hasMany(CustomerLoyalty::className(), ['category_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCompany()
    {
        return $this->hasOne(Company::className(), ['id' => 'company_id']);
    }

    /**
     * @return string fontColor for current backgroundColor
     */
    public function getFontColor() {
        return self::calcColor($this->color);
    }

    /**
     * @param $color
     * @return string
     */
    public static function calcColor($color) {
        $r = hexdec(substr($color, 1, 2));
        $g = hexdec(substr($color, 3, 2));
        $b = hexdec(substr($color, 5, 2));
        $a = 1 - ( 0.299 * $r + 0.587 * $g + 0.114 * $b)/255;
        return ($a < 0.5 ? '#000' : '#fff');
    }

    /**
     * Return map of all categories with background and font color
     *
     * @return array
     */
    public static function getCategoryMapSelect2() {
        $array = ArrayHelper::map(CustomerCategory::find()->company()->all(),'id','name');
        $map = [];
        foreach($array as $key => $value) {
            $category = CustomerCategory::find()->where(['id' => $key])->company()->one();
            $item = [
                'name' => $value,
                'back-color' => $category->color,
                'font-color' => $category->fontColor,
            ];
            $map[$key] = $item;
        }
        return $map;
    }

    public function beforeSave($insert) {
        $this->name = str_replace('\'', '"', $this->name);
        return true;
    }

    /**
     * @inheritdoc
     */
    public static function find()
    {
        return new CustomerCategoryQuery(get_called_class());
    }

    /**
     * @inheritdoc
     */
    public function beforeDelete()
    {
        $this->unlinkAll('customers', true);
        $this->unlinkAll('customerLoyalties');
        return parent::beforeDelete();
    }

    /**
     * Returns mapped categories
     * @return array
     */
    public static function map() {
        return ArrayHelper::map(self::find()->company()->all(), 'id', 'name');
    }

}
