<?php

namespace core\models\finance;

use core\models\company\Company;
use core\models\customer\CompanyCustomer;
use core\models\division\Division;
use core\models\finance\query\CashflowQuery;
use core\models\finance\query\CostItemQuery;
use core\models\order\Order;
use core\models\order\query\OrderQuery;
use core\models\Staff;
use core\models\StaffPayment;
use core\models\user\User;
use core\models\warehouse\query\SaleQuery;
use core\models\warehouse\Sale;
use lhs\Yii2SaveRelationsBehavior\SaveRelationsBehavior;
use Yii;

/**
 * This is the model class for table "{{%company_cashflows}}".
 *
 * @property integer $id
 * @property string $date
 * @property integer $cost_item_id
 * @property integer $cash_id
 * @property integer $receiver_mode
 * @property integer $contractor_id
 * @property integer $customer_id
 * @property integer $staff_id
 * @property integer $status
 * @property integer $value
 * @property string $comment
 * @property integer $user_id
 * @property integer $company_id
 * @property integer $division_id
 * @property integer $order_id
 *
 * @property CompanyCashflowPayment[] $payments
 * @property Company $company
 * @property CompanyCash $cash
 * @property CompanyCashflowProduct[] $products
 * @property CompanyCashflowService[] $services
 * @property CompanyContractor $contractor
 * @property CompanyCostItem $costItem
 * @property CompanyCustomer $customer
 * @property Staff $staff
 * @property StaffPayment $salaryPayment
 * @property User $user
 * @property Order $order
 */
class CompanyCashflow extends \yii\db\ActiveRecord
{
    const RECEIVER_CONTRACTOR = 0;
    const RECEIVER_CUSTOMER = 1;
    const RECEIVER_STAFF = 2;

    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 1;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%company_cashflows}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['receiver_mode', 'default', 'value' => self::RECEIVER_CONTRACTOR],
            [['date', 'cost_item_id', 'cash_id', 'division_id', 'value'], 'required'],
            [['date'], 'safe'],
            [['cost_item_id', 'cash_id', 'receiver_mode', 'contractor_id', 'customer_id', 'division_id',
                'staff_id', 'value', 'user_id', 'company_id', 'status'], 'integer'],
            [['comment'], 'string'],
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            ['status', 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_INACTIVE]],
            [['division_id'], 'exist', 'skipOnError' => true, 'targetClass' => Division::className(), 'targetAttribute' => ['division_id' => 'id']],
            [['company_id'], 'exist', 'skipOnError' => true, 'targetClass' => Company::className(), 'targetAttribute' => ['company_id' => 'id']],
            [['cash_id'], 'exist', 'skipOnError' => true, 'targetClass' => CompanyCash::className(), 'targetAttribute' => ['cash_id' => 'id']],
            [['contractor_id'], 'exist', 'skipOnError' => true, 'targetClass' => CompanyContractor::className(), 'targetAttribute' => ['contractor_id' => 'id']],
            [['cost_item_id'], 'exist', 'skipOnError' => true, 'targetClass' => CompanyCostItem::className(), 'targetAttribute' => ['cost_item_id' => 'id']],
            [['customer_id'], 'exist', 'skipOnError' => true, 'targetClass' => CompanyCustomer::className(), 'targetAttribute' => ['customer_id' => 'id']],
            [['staff_id'], 'exist', 'skipOnError' => true, 'targetClass' => Staff::className(), 'targetAttribute' => ['staff_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'            => Yii::t('app', 'ID'),
            'date'          => Yii::t('app', 'Date'),
            'cash_id'       => Yii::t('app', 'Cash'),
            'comment'       => Yii::t('app', 'Comment'),
            'company_id'    => Yii::t('app', 'Company'),
            'contractor_id' => Yii::t('app', 'Contractor'),
            'cost_item_id'  => Yii::t('app', 'Cost Item'),
            'customer_id'   => Yii::t('app', 'Customer'),
            'division_id'   => Yii::t('app', 'Division'),
            'receiver_mode' => Yii::t('app', 'Receiver Mode'),
            'staff_id'      => Yii::t('app', 'Staff ID'),
            'status'        => Yii::t('app', 'Status'),
            'value'         => Yii::t('app', 'Value Cashflow'),
            'user_id'       => Yii::t('app', 'User'),
            'created_by'    => Yii::t('app', 'Created by'),
            'updated_by'    => Yii::t('app', 'Updated by'),
            'created_at'    => Yii::t('app', 'Created at'),
            'updated_at'    => Yii::t('app', 'Updated at'),
            'is_deleted'    => Yii::t('app', 'Is deleted'),
        ];
    }

    /**
     * Returns behaviors for this model.
     * @return array of behaviors
     */
    public function behaviors()
    {
        return [
            \yii\behaviors\TimestampBehavior::class,
            \yii\behaviors\BlameableBehavior::class,
            [
                'class' => \yii2tech\ar\softdelete\SoftDeleteBehavior::class,
                'softDeleteAttributeValues' => [
                    'is_deleted' => true,
                ],
            ],
            \common\components\HistoryBehavior::class,
            [
                'class'     => SaveRelationsBehavior::class,
                'relations' => ['order', 'products', 'services', 'payments'],
            ],
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
    public function getCash()
    {
        return $this->hasOne(CompanyCash::className(), ['id' => 'cash_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getContractor()
    {
        return $this->hasOne(CompanyContractor::className(), ['id' => 'contractor_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCostItem()
    {
        return $this->hasOne(CompanyCostItem::className(), ['id' => 'cost_item_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCustomer()
    {
        return $this->hasOne(CompanyCustomer::className(), ['id' => 'customer_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDivision()
    {
        return $this->hasOne(Division::className(), ['id' => 'division_id']);
    }

    /**
     * @return \yii\db\ActiveQuery|query\CashflowPaymentQuery
     */
    public function getPayments()
    {
        return $this->hasMany(CompanyCashflowPayment::class, ['cashflow_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery|query\CashflowProductQuery
     */
    public function getProducts()
    {
        return $this->hasMany(CompanyCashflowProduct::class, ['cashflow_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery|query\CashflowServiceQuery
     */
    public function getServices()
    {
        return $this->hasMany(CompanyCashflowService::class, ['cashflow_id' => 'id']);
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
    public function getSalaryPayment()
    {
        return $this->hasOne(StaffPayment::className(), ['id' => 'staff_payment_id'])
            ->viaTable('{{%company_cashflow_salaries}}', ['cashflow_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery|OrderQuery
     */
    public function getOrder()
    {
        return $this->hasOne(Order::class, ['id' => 'order_id']);
    }

    /**
     * @return \yii\db\ActiveQuery|SaleQuery
     */
    public function getSale()
    {
        return $this->hasOne(Sale::class, ['id' => 'sale_id'])
            ->viaTable('{{%company_cashflow_sales}}', ['cashflow_id' => 'id']);
    }

    /**
     * @inheritdoc
     */
    public static function find()
    {
        return new CashflowQuery(get_called_class());
    }

    /**
     * @param string  $date
     * @param integer $cash_id
     * @param string  $comment
     * @param integer $company_id
     * @param integer $contractor_id
     * @param integer $cost_item_id
     * @param integer $company_customer_id
     * @param integer $division_id
     * @param integer $receiver_mode
     * @param integer $staff_id
     * @param integer $value
     * @param integer $user_id
     *
     * @return CompanyCashflow
     */
    public static function add(
        $date,
        $cash_id,
        $comment,
        $company_id,
        $contractor_id,
        $cost_item_id,
        $company_customer_id,
        $division_id,
        $receiver_mode,
        $staff_id,
        $value,
        $user_id
    ) {
        $model = new CompanyCashflow();
        $model->date = $date;
        $model->cash_id = $cash_id;
        $model->comment = $comment;
        $model->company_id = $company_id;
        $model->contractor_id = $contractor_id;
        $model->cost_item_id = $cost_item_id;
        $model->customer_id = $company_customer_id;
        $model->division_id = $division_id;
        $model->receiver_mode = $receiver_mode;
        $model->staff_id = $staff_id;
        $model->value = intval($value);
        $model->user_id = $user_id;
        return $model;
    }

    public function setOrderRelation(Order $order)
    {
        $this->order = $order;
    }

    /**
     * @param $date
     * @param $division_id
     * @param $staff_id
     * @param $value
     * @param $company_id
     * @param $user_id
     * @return CompanyCashflow
     */
    public static function addSalary($date, $division_id, $staff_id, $value, $company_id, $user_id)
    {
        $cash_id = CompanyCash::find()->select("id")->company($company_id)->scalar();
        $cost_item_id = CompanyCostItem::find()->select("id")->isSalary()->company($company_id)->scalar();
        $receiver_mode = CompanyCashflow::RECEIVER_STAFF;

        return self::add(
            $date,
            $cash_id,
            null,
            $company_id,
            null,
            $cost_item_id,
            null,
            $division_id,
            $receiver_mode,
            $staff_id,
            $value,
            $user_id
        );
    }

    /**
     * @deprecated
     * @param $cash_id
     * @param $company_customer_id
     * @param $date
     * @param $division_id
     * @param $staff_id
     * @param $value
     * @param User $user
     * @return CompanyCashflow
     */
    public static function addSoldProduct($cash_id, $company_customer_id, $date, $division_id, $staff_id, $value, User $user)
    {
        $company_id = $user->company_id;
        $cost_item_id = CompanyCostItem::find()->select("id")->isProductSale()->company($user->company_id)->scalar();
        $receiver_mode = CompanyCashflow::RECEIVER_STAFF;
        $user_id = $user->id;

        return self::add($date, $cash_id, NULL, $company_id, NULL, $cost_item_id, $company_customer_id,
            $division_id, $receiver_mode, $staff_id, $value, $user_id);
    }

    /**
     * @param string  $date
     * @param integer $cash_id
     * @param string  $comment
     * @param integer $company_id
     * @param integer $contractor_id
     * @param integer $cost_item_id
     * @param integer $company_customer_id
     * @param integer $division_id
     * @param integer $receiver_mode
     * @param integer $staff_id
     * @param integer $value
     * @param integer $user_id
     */
    public function edit(
        $date,
        $cash_id,
        $comment,
        $company_id,
        $contractor_id,
        $cost_item_id,
        $company_customer_id,
        $division_id,
        $receiver_mode,
        $staff_id,
        $value,
        $user_id
    )
    {
        $this->date = $date;
        $this->cash_id = $cash_id;
        $this->comment = $comment;
        $this->company_id = $company_id;
        $this->contractor_id = $contractor_id;
        $this->cost_item_id = $cost_item_id;
        $this->customer_id = $company_customer_id;
        $this->division_id = $division_id;
        $this->receiver_mode = $receiver_mode;
        $this->staff_id = $staff_id;
        $this->value = $value;
        $this->user_id = $user_id;
    }

    /**
     * Cancel cashflow
     */
    public function cancel()
    {
        $this->status = self::STATUS_INACTIVE;
    }

    /**
     * Enable cashflow
     */
    public function enable()
    {
        $this->status = self::STATUS_ACTIVE;
    }

    /**
     * Is model editable
     */
    public function isEditable()
    {
        return !isset($this->order) && !isset($this->salaryPayment)
            && empty($this->products)
            && $this->costItem->cost_item_type != CompanyCostItem::COST_ITEM_TYPE_DEPOSIT_INCOME
            && $this->costItem->cost_item_type != CompanyCostItem::COST_ITEM_TYPE_DEPOSIT_EXPENSE
            && $this->costItem->cost_item_type != CompanyCostItem::COST_ITEM_TYPE_INCOME_CASH_TRANSFER
            && $this->costItem->cost_item_type != CompanyCostItem::COST_ITEM_TYPE_EXPENSE_CASH_TRANSFER
            && $this->costItem->cost_item_type != CompanyCostItem::COST_ITEM_TYPE_DEBT_PAYMENT;
    }

    public function isActive(): bool
    {
        return ($this->status === self::STATUS_ACTIVE && !$this->is_deleted);
    }

    /**
     * @return float|int
     */
    public function getDiscount()
    {
        $total = array_reduce($this->services, function (int $total = 0, CompanyCashflowService $service) {
            return $total + ($service->price * $service->discount / 100);
        }, 0);

        return array_reduce($this->products, function (int $total = 0, CompanyCashflowProduct $product) {
            return $total + ($product->price * $product->quantity * $product->discount / 100);
        }, $total);
    }

    /**
     * @return int
     */
    public function getValue()
    {
        if ($this->costItem->isExpense()) {
            return (-1) * $this->value;
        }
        return $this->value;
    }

    /**
     * @param string $separator
     * @return string
     */
    public function getItemsTitle(string $separator = ', ')
    {
        $data = [];
        foreach ($this->services as $service) {
            $data[] = $service->service->service_name . ' (' . $service->quantity . ')';
        }
        foreach ($this->products as $product) {
            $data[] = $product->product->name . ' (' . $product->quantity . ')';
        }
        return implode($separator, $data);
    }

    /**
     * @return array
     */
    public function fields()
    {
        return [
            'id',
            'cash_id',
            'contractor_id',
            'cost_item_id',
            'customer_id',
            'date',
            'division_id',
            'staff_id',
            'value'
        ];
    }

    /**
     * @return array
     */
    public function extraFields()
    {
        return [
            'cash',
            'contractor',
            'costItem',
            'customer',
            'division',
            'staff'
        ];
    }

    /**
     * @return bool
     */
    public function beforeDelete()
    {
        if (parent::beforeDelete()) {
            $this->unlinkAll('payments', true);
            $this->unlinkAll('products', true);
            $this->unlinkAll('services', true);
            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    public function isDeletableDebtPayment()
    {
        if ($this->costItem->cost_item_type === CompanyCostItem::COST_ITEM_TYPE_DEBT_PAYMENT
            && $this->order && $this->order->isFinished()) {
            // if after debt payment order was refund then cannot be deleted
            $wasRefund = $this->order->getCashflows()->active()->joinWith([
                    'costItem' => function (CostItemQuery $query) {
                        return $query->isRefund();
                    }
                ]
            )->andWhere(['>=', 'created_at', $this->created_at])->exists();

            return !$wasRefund;
        }
        return false;
    }
}
