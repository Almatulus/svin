<?php

namespace core\models\customer;

use common\components\HistoryBehavior;
use core\helpers\GenderHelper;
use core\helpers\order\OrderConstants;
use core\models\company\Company;
use core\models\customer\query\CompanyCustomerQuery;
use core\models\File;
use core\models\InsuranceCompany;
use core\models\order\Order;
use core\models\order\OrderDocument;
use core\models\order\query\OrderQuery;
use core\services\dto\CustomerInsuranceData;
use DateTime;
use lhs\Yii2SaveRelationsBehavior\SaveRelationsBehavior;
use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "crm_company_customers".
 *
 * @property integer $id
 * @property integer $discount
 * @property integer $discount_granted_by
 * @property integer $rank
 * @property boolean $sms_birthday
 * @property boolean $sms_exclude
 * @property integer $customer_id
 * @property integer $company_id
 * @property string $created_time
 * @property string $updated_time
 * @property integer $created_user_id
 * @property integer $updated_user_id
 * @property boolean $is_active
 * @property string $comments
 * @property string $address
 * @property string $city
 * @property integer $source_id
 * @property integer $balance
 * @property string $job
 * @property string $employer
 * @property float $cashback_balance
 * @property integer $cashback_percent
 * @property integer $insurance_company_id
 * @property string $insurance_policy_number
 * @property string $insurer
 * @property string $insurance_expire_date
 * @property integer $revenue
 * @property string $medical_record_id
 *
 * @property Order $lastOrder
 * @property Company $company
 * @property Customer $customer
 * @property Order[] $orders
 * @property CustomerCategory[] $categories
 * @property CompanyCustomerPhone[] $phones
 * @property CustomerSource $source
 * @property InsuranceCompany $insuranceCompany
 * @property CompanyCustomerHistory[] $histories
 */
class CompanyCustomer extends \yii\db\ActiveRecord
{
    const RANK_NONE = 0;
    const RANK_COPPER = 1;
    const RANK_SILVER = 2;
    const RANK_GOLD = 3;

    const GRANTED_BY_LOYALTY = 0;
    const GRANTED_BY_CATEGORY = 1;

    private $_finishedOrdersCount = null;
    private $_revenue = null;

    /**
     * @param Customer $customer
     * @param integer  $company_id
     * @param integer  $discount
     * @param boolean  $sms_birthday
     * @param boolean  $sms_exclude
     * @param string   $comments
     * @param integer  $source_id
     * @param string   $address
     * @param integer  $city_id
     * @param integer  $balance
     * @param null     $job
     * @param null     $employer
     * @param integer  $rank
     * @param integer  $discount_granted_by
     * @param string $medical_record_id
     *
     * @return CompanyCustomer
     */
    public static function add(
        Customer $customer,
        $company_id,
        $discount = 0,
        $sms_birthday = true,
        $sms_exclude = false,
        $comments = null,
        $source_id = null,
        $address = null,
        $city_id = null,
        $balance = 0,
        $job = null,
        $employer = null,
        $rank = null,
        $discount_granted_by = null,
        $medical_record_id = null
    ) {
        CompanyCustomer::guardNotExists($customer, $company_id);

        $model                      = new CompanyCustomer();
        $model->customer            = $customer;
        $model->is_active           = true;
        $model->discount            = $discount;
        $model->discount_granted_by = $discount_granted_by;
        $model->sms_birthday        = $sms_birthday;
        $model->sms_exclude         = $sms_exclude;
        $model->rank                = $rank ?: self::RANK_NONE;
        $model->company_id          = $company_id;
        $model->comments            = $comments;
        $model->source_id           = $source_id;
        $model->address             = $address;
        $model->city                = $city_id;
        $model->balance             = $balance;
        $model->job                 = $job;
        $model->employer            = $employer;
        $model->medical_record_id   = $medical_record_id;

        return $model;
    }

    /**
     * @param string $address
     * @param integer $city
     * @param integer $source_id
     * @param string $comments
     * @param boolean $sms_birthday
     * @param boolean $sms_exclude
     * @param integer $balance
     * @param string $job
     * @param string $employer
     * @param integer $discount
     * @param $cashback_percent
     * @param string $medical_record_id
     */
    public function edit(
        $address,
        $city,
        $source_id,
        $comments,
        $sms_birthday,
        $sms_exclude,
        $balance,
        $job,
        $employer,
        $discount,
        $cashback_percent,
        $medical_record_id
    ) {
        $this->city             = $city;
        $this->sms_birthday     = $sms_birthday;
        $this->sms_exclude      = $sms_exclude;
        $this->comments         = $comments;
        $this->source_id        = $source_id;
        $this->address          = $address;
        $this->balance          = $balance;
        $this->job              = $job;
        $this->employer         = $employer;
        $this->discount         = $discount;
        $this->cashback_percent = $cashback_percent;
        $this->medical_record_id = $medical_record_id;
    }

    /**
     * @param integer $debt
     */
    public function addDebt($debt)
    {
        $this->balance = $this->balance - $debt;
    }

    /**
     * @param integer $balance
     */
    public function addBalance($balance)
    {
        $this->balance += $balance;
    }

    /**
     * @param CustomerSource $source
     */
    public function setCustomerSource(CustomerSource $source)
    {
        $this->source = $source;
    }

    /**
     * @param InsuranceCompany $insuranceCompany
     */
    public function setCustomerInsuranceCompany(InsuranceCompany $insuranceCompany)
    {
        $this->insuranceCompany = $insuranceCompany;
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%company_customers}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['customer_id', 'company_id'], 'required'],
            [['rank', 'customer_id', 'company_id', 'source_id', 'discount_granted_by'], 'integer'],
            [['sms_birthday', 'sms_exclude'], 'boolean'],
            [['sms_birthday', 'sms_exclude', 'comments'], 'safe'],
            [['discount'], 'integer', 'min' => 0, 'max' => 100],
            [['discount'], 'default', 'value' => 0],
            [['address', 'city', 'employer', 'job'], 'safe'],
            [['medical_record_id', 'insurer'], 'string', 'max' => 255],
            ['insurance_policy_number', 'string', 'max' => 255],
            ['insurance_expire_date', 'date', 'format' => 'yyyy-MM-dd'],

            ['cashback_balance', 'number', 'min' => 0],
            ['cashback_percent', 'number', 'min' => 0, 'max' => 100],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'name'                    => Yii::t('app', 'Customer Name'),
            'id'                      => Yii::t('app', 'ID'),
            'discount'                => Yii::t('app', 'Discount'),
            'rank'                    => Yii::t('app', 'Rank'),
            'sms_birthday'            => Yii::t('app', 'SMS birthday'),
            'sms_exclude'      => Yii::t('app', 'SMS exclude'),
            'customer_id'      => Yii::t('app', 'Customer ID'),
            'company_id'       => Yii::t('app', 'Company ID'),
            'categories'       => Yii::t('app', 'Categories'),
            'created_time'     => Yii::t('app', 'Created Time'),
            'moneySpent'       => Yii::t('app', 'Money spent'),
            'comments'                => Yii::t('app', 'Comments'),
            'address'                 => Yii::t('app', 'Address'),
            'city'                    => Yii::t('app', 'City'),
            'source_id'               => Yii::t('app', 'Customer Source'),
            'balance'                 => Yii::t('app', 'Balance'),
            'job'                     => Yii::t('app', 'Job'),
            'employer'                => Yii::t('app', 'Employer'),
            'cashback_balance'        => Yii::t('app', "Cashback"),
            'cashback_percent'        => Yii::t('app', "Cashback Percent"),
            'insurer'                 => Yii::t('app', 'Insurer'),
            'insurance_policy_number' => Yii::t('app', 'Insurance policy number'),
            'insurance_expire_date'   => Yii::t('app', 'Insurance is valid until'),
            'medical_record_id'       => Yii::t('app', 'Number of medical record')
        ];
    }

    /**
     * @inheritdoc
     */
    public function fields()
    {
        $customer = $this->customer;

        return [
            'id',
            'name'               => function () use ($customer) {
                return strval($customer->name);
            },
            'lastname'           => function () use ($customer) {
                return strval($customer->lastname);
            },
            'patronymic'         => function () use ($customer) {
                return strval($customer->patronymic);
            },
            'fullname'           => function () use ($customer) {
                return $customer->getFullName();
            },
            'phone'              => function () use ($customer) {
                return strval($customer->phone);
            },
            'email'              => function () use ($customer) {
                return $customer->email;
            },
            'gender'             => function () use ($customer) {
                return $customer->gender;
            },
            'gender_title'       => function () use ($customer) {
                return GenderHelper::getGenderLabel($customer->gender);
            },
            'birth_date'         => function () use ($customer) {
                return $customer->birth_date;
            },
            'iin'                => function () use ($customer) {
                return $customer->iin;
            },
            'id_card_number'     => function () use ($customer) {
                return $customer->id_card_number;
            },
            'image_url' => function () use ($customer) {
                return $customer->image_id ? $customer->image->getAvatarImageUrl() : null;
            },
            'address',
            'balance',
            'city',
            'comments',
            'discount',
            'employer',
            'job',
            'sms_birthday',
            'sms_birthday_title' => function () {
                return intval($this->sms_birthday) ? "Да" : "Нет";
            },
            'sms_exclude',
            'sms_exclude_title'  => function () {
                return intval($this->sms_exclude) ? "Да" : "Нет";
            },
            'source_id',
            'cashback_percent',
            'cashback_balance',
            'insurance_company_id',
            'insurer',
            'insurance_policy_number',
            'insurance_expire_date',
            // To remove
            'finishedOrders'     => function () {
                return $this->getOrders()->finished()->count();
            },
            'debt',
            'deposit',
            'revenue',
            'medical_record_id'
        ];
    }

    /**
     * @inheritdoc
     */
    public function extraFields()
    {
        return [
            'customer',
            'categories',
            'files' => function () {
                return File::find()
                    ->innerJoin('{{%order_files}}', '{{%order_files}}.file_id = {{%s3_files}}.id')
                    ->leftJoin('{{%orders}}', '{{%orders}}.id = order_id')
                    ->leftJoin('{{%company_customers}}', '{{%company_customers}}.id = company_customer_id')
                    ->where(['company_customer_id' => $this->id])
                    ->all();
            },
            'documents'        => function () {
                return OrderDocument::find()
                    ->leftJoin('{{%orders}}', '{{%orders}}.id = order_id')
                    ->leftJoin('{{%company_customers}}', '{{%company_customers}}.id = company_customer_id')
                    ->where(['company_customer_id' => $this->id])
                    ->all();
            },
            'debt',
            'deposit',
            'revenue',
            'source',
            'canceledOrders'   => function () {
                return $this->getOrders()->canceled()->count();
            },
            'finishedOrders'   => function () {
                return $this->getOrders()->finished()->count();
            },
            'lastOrder',
            'orders'           => function () {
                return $this->getOrders()->orderBy("{{%orders}}.datetime DESC")->all();
            },

            // Refactor JS to remove below
            'categories_title' => function () {
                return implode(", ", ArrayHelper::getColumn($this->categories, 'name', false));
            },
            'source_id_title'  => function () {
                return $this->source ? $this->source->name : Yii::t('app', 'Unknown');
            },
            'lastVisit'        => function () {
                $lastVisitDatetime = $this->getLastVisitDateTime();
                if ($lastVisitDatetime !== null) {
                    $lastVisit = $lastVisitDatetime->format("d.m.Y")." - ".$this->lastOrder->staff->getFullName();
                } else {
                    $lastVisit = "Посещения отсутствуют";
                }

                return $lastVisit;
            },
            'phones'
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCategories()
    {
        return $this->hasMany(CustomerCategory::className(), ['id' => 'category_id'])
            ->viaTable('{{%company_customer_category_map}}', ['company_customer_id' => 'id']);
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
    public function getCustomer()
    {
        return $this->hasOne(Customer::className(), ['id' => 'customer_id']);
    }

    /**
     * @return \yii\db\ActiveQuery|OrderQuery
     */
    public function getOrders()
    {
        return $this->hasMany(Order::className(), ['company_customer_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPhones()
    {
        return $this->hasMany(CompanyCustomerPhone::className(), ['company_customer_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSource()
    {
        return $this->hasOne(CustomerSource::className(), ['id' => 'source_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getInsuranceCompany()
    {
        return $this->hasOne(InsuranceCompany::className(), ['id' => 'insurance_company_id']);
    }

    /**
     * Returns customer last order
     * @return \yii\db\ActiveQuery
     */
    public function getLastOrder()
    {
        return $this->hasOne(Order::className(), ['company_customer_id' => 'id'])
            ->status(OrderConstants::STATUS_FINISHED)
            ->orderDatetime(SORT_DESC);
    }

    /**
     * @return int
     */
    public function getFinishedOrdersCount()
    {
        if ($this->_finishedOrdersCount === null) {
            $this->_finishedOrdersCount = $this->getOrders()->finished()->count();
        }
        return $this->_finishedOrdersCount;
    }

    /**
     * Calculates how much money did the CompanyCustomer spend with discounts of each order
     * @return integer
     */
    public function getRevenue()
    {
        if ($this->_revenue === null) {
            $this->_revenue = intval($this->getOrders()->finished()->sum('{{%orders}}.price'));
        }

        return $this->_revenue;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getHistories()
    {
        return $this->hasMany(CompanyCustomerHistory::className(), ['row_id' => 'id']);
//        $query = CompanyCustomerHistory::find()
//            ->andWhere([
//                'OR',
//                ['AND', ['table_name' => self::tableName()],['row_id' => $this->id]],
//                ['AND', ['table_name' => Customer::tableName()],['row_id' => $this->customer_id]],
//            ]);
//        $query->multiple = true;
//        return $query;
    }

    /**
     * @param $amount
     */
    public function addCashback($amount)
    {
        $this->cashback_balance += $amount;
    }

    /**
     * @param $amount
     * @param bool $ignoreInsufficientBalance
     */
    public function subtractCashback($amount, $ignoreInsufficientBalance = false)
    {
        if (!$ignoreInsufficientBalance && $this->cashback_balance - $amount < 0) {
            throw new \DomainException("Customer have less cashback than required.");
        }
        $this->cashback_balance -= $amount;
    }

    /**
     * @param $amount
     * @param int|null $cashback_percent
     * @return float|int
     */
    public function estimateCashback($amount, int $cashback_percent = null)
    {
        if ($cashback_percent == null) {
            $cashback_percent = $this->cashback_percent;
        }

        return intval($cashback_percent * $amount / 100);
    }

    /**
     * Returns customer last visit
     * @return DateTime | null
     */
    public function getLastVisitDateTime()
    {
        return $this->lastOrder == null ? null : new DateTime($this->lastOrder->datetime);
    }

    /**
     * Returns deposit
     *
     * @return integer
     */
    public function getDeposit()
    {
        return max($this->balance, 0);
    }

    /**
     * Returns debt
     *
     * @return integer
     */
    public function getDebt()
    {
        return min($this->balance, 0);
    }

    /**
     * @inheritdoc
     */
    public static function find()
    {
        return new CompanyCustomerQuery(get_called_class());
    }

    /**
     * @deprecated
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ( ! $this->isNewRecord) {
                if ($this->discount < $this->oldAttributes['discount']) {
                    CustomerRequest::sendTemplateRequest($this, CustomerRequestTemplate::TYPE_NOTIFY_CLIENT_DISCOUNT_EXPIRE, [
                        'DISCOUNT' => $this->discount
                    ]);
                } elseif ($this->discount > $this->oldAttributes['discount']) {
                    CustomerRequest::sendTemplateRequest($this, CustomerRequestTemplate::TYPE_NOTIFY_CLIENT_NEW_DISCOUNT, [
                        'DISCOUNT' => $this->discount
                    ]);
                }
            }
            return true;
        }
        return false;
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class'              => TimestampBehavior::className(),
                'createdAtAttribute' => 'created_time',
                'updatedAtAttribute' => 'updated_time',
                'value'              => function () {
                    return date('Y-m-d H:i:s');
                },
            ],
            [
                'class'              => BlameableBehavior::className(),
                'createdByAttribute' => 'created_user_id',
                'updatedByAttribute' => 'updated_user_id',
            ],
            [
                'class'     => SaveRelationsBehavior::className(),
                'relations' => ['customer', 'source', 'insuranceCompany', 'categories'],
            ],
            HistoryBehavior::className(),
        ];
    }

    /**
     * @param Customer $customer
     * @param integer $company_id
     */
    private static function guardNotExists(Customer $customer, $company_id)
    {
        if (!$customer->isNewRecord && CompanyCustomer::findOne(['customer_id' => $customer->id, 'company_id' => $company_id])) {
            throw new \DomainException('Company Customer already exists');
        }
    }

    /**
     * @param CustomerInsuranceData $customerInsuranceData
     */
    public function setInsuranceData(CustomerInsuranceData $customerInsuranceData)
    {
        $this->insurance_expire_date = $customerInsuranceData->getInsuranceExpireDate();
        $this->insurer = $customerInsuranceData->insurer;
        $this->insurance_policy_number = $customerInsuranceData->insurance_policy_number;
    }

    /**
     * restore product
     */
    public function restore()
    {
        $this->is_active = true;
    }

    /**
     * Soft delete
     */
    public function softDelete()
    {
        $this->is_active = false;
    }

    public static function map()
    {
        return \yii\helpers\ArrayHelper::map(self::find()->joinWith('customer')->company()->all(), 'id', 'customer.fullInfo');
    }

    /**
     * @return bool
     */
    public function hasDebt()
    {
        return $this->balance < 0;
    }
}
