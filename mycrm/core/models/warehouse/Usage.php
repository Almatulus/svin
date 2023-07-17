<?php

namespace core\models\warehouse;

use core\models\company\Company;
use core\models\customer\CompanyCustomer;
use core\models\division\Division;
use core\models\order\Order;
use core\models\Staff;
use core\models\warehouse\query\UsageQuery;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * ToDo delete redundant company_id, there is division_id
 * This is the model class for table "{{%warehouse_use}}".
 *
 * @property integer $id
 * @property integer $company_id
 * @property integer $company_customer_id
 * @property integer $discount
 * @property integer $division_id
 * @property integer $staff_id
 * @property integer $status
 * @property string $created_at
 * @property string $updated_at
 * @property string $comments
 *
 * @property Company $company
 * @property CompanyCustomer $companyCustomer
 * @property Order $order
 * @property Staff $staff
 * @property UsageProduct[] $usageProducts
 */
class Usage extends \yii\db\ActiveRecord
{
    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_CANCELED = 2;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%warehouse_usage}}';
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            'timestampBehavior' => [
                'class' => TimestampBehavior::className(),
                'value' => new Expression('NOW()')
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['division_id'], 'required'],
            [['company_id', 'company_customer_id', 'division_id', 'staff_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],

            [['discount'], 'default', 'value' => 0],
            [['discount'], 'integer', 'min' => 0, 'max' => 100],

            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            ['status', 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_INACTIVE, self::STATUS_CANCELED]],

            ['comments', 'string'],

            [['company_id'], 'exist', 'skipOnError' => true, 'targetClass' => Company::className(), 'targetAttribute' => ['company_id' => 'id']],
            [['company_customer_id'], 'exist', 'skipOnError' => true, 'targetClass' => CompanyCustomer::className(), 'targetAttribute' => ['company_customer_id' => 'id']],
            [['staff_id'], 'exist', 'skipOnError' => true, 'targetClass' => Staff::className(), 'targetAttribute' => ['staff_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'                  => Yii::t('app', 'ID'),
            'company_id'          => Yii::t('app', 'Company ID'),
            'company_customer_id' => Yii::t('app', 'Customer'),
            'division_id'         => Yii::t('app', 'Division ID'),
            'staff_id'            => Yii::t('app', 'Staff ID'),
            'created_at'          => Yii::t('app', 'Usage date'),
            'updated_at'          => Yii::t('app', 'Updated at'),
            'sum'                 => Yii::t('app', 'Sum'),
            'comments'            => Yii::t('app', 'Comments')
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
    public function getCompanyCustomer()
    {
        return $this->hasOne(CompanyCustomer::className(), ['id' => 'company_customer_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDivision()
    {
        return $this->hasOne(Division::className(), ['id' => 'division_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrder()
    {
        return $this->hasOne(Order::class, ['id' => 'order_id'])->viaTable('{{%order_usage}}', [
            'usage_id' => 'id'
        ]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStaff()
    {
        return $this->hasOne(Staff::className(), ['id' => 'staff_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUsageProducts()
    {
        return $this->hasMany(UsageProduct::className(), ['usage_id' => 'id']);
    }

    /**
     * @return float
     */
    public function getSum()
    {
        $sum = 0;
        foreach ($this->usageProducts as $key => $usageItem) {
            $sum += ($usageItem->selling_price * $usageItem->quantity);
        }
        return $sum * (100 - $this->discount) / 100;
    }

    /**
     * @param $company_id
     * @param $company_customer_id
     * @param $discount
     * @param $division_id
     * @param $staff_id
     * @param $comments
     *
     * @return Usage
     */
    public static function add($company_id, $company_customer_id, $discount, $division_id, $staff_id, $comments)
    {
        $model = new Usage();
        $model->company_id = $company_id;
        $model->company_customer_id = $company_customer_id;
        $model->discount = $discount;
        $model->division_id = $division_id;
        $model->staff_id = $staff_id;
        $model->comments = $comments;
        return $model;
    }

    /**
     * @param UsageProduct[] $usageProducts
     */
    public function setUsageProducts($usageProducts)
    {
        $this->usageProducts = $usageProducts;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->status == self::STATUS_ACTIVE;
    }

    /**
     * @return bool
     */
    public function isCanceled()
    {
        return $this->status == self::STATUS_CANCELED;
    }

    /**
     * Cancel usage
     */
    public function enable()
    {
        $this->status = self::STATUS_ACTIVE;
    }

    /**
     * Cancel usage
     */
    public function cancel()
    {
        $this->status = self::STATUS_CANCELED;
    }

    /**
     * Cancel usage
     */
    public function softDelete()
    {
        $this->status = self::STATUS_INACTIVE;
    }

    /**
     * @inheritdoc
     */
    public static function find()
    {
        return new UsageQuery(get_called_class());
    }
}
