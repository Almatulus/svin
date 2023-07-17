<?php

namespace core\models\warehouse;

use core\models\company\Company;
use core\models\customer\CompanyCustomer;
use core\models\division\Division;
use core\models\finance\CompanyCash;
use core\models\finance\CompanyCashflow;
use core\models\Payment;
use core\models\Staff;
use core\models\warehouse\query\SaleQuery;
use Yii;

/**
 * This is the model class for table "{{%warehouse_sale}}".
 *
 * @property integer $id
 * @property integer $cash_id
 * @property integer $company_customer_id
 * @property integer $discount
 * @property integer $division_id
 * @property double $paid
 * @property double $payment_id
 * @property integer $staff_id
 * @property string $sale_date
 *
 * @property CompanyCash $cash
 * @property CompanyCashflow $cashflow
 * @property CompanyCustomer $companyCustomer
 * @property Division $division
 * @property Staff $staff
 * @property SaleProduct[] $saleProducts
 */
class Sale extends \yii\db\ActiveRecord
{
    /**
     * @param Division $division
     * @param $cash_id
     * @param $company_customer_id
     * @param $discount
     * @param $paid
     * @param $payment_id
     * @param $sale_date
     * @param $staff_id
     * @return Sale
     */
    public static function create(Division $division, $cash_id, $company_customer_id, $discount,
                                  $paid, $payment_id, $sale_date, $staff_id)
    {
        $model = new self();
        $model->populateRelation('division', $division);
        $model->cash_id = $cash_id;
        $model->company_customer_id = $company_customer_id;
        $model->discount = $discount;
        $model->paid = $paid;
        $model->payment_id = $payment_id;
        $model->sale_date = $sale_date;
        $model->staff_id = $staff_id;
        return $model;
    }

    /**
     * @param Division $division
     * @param $cash_id
     * @param $company_customer_id
     * @param $discount
     * @param $paid
     * @param $payment_id
     * @param $sale_date
     * @param $staff_id
     */
    public function edit(Division $division, $cash_id, $company_customer_id, $discount,
                         $paid, $payment_id, $sale_date, $staff_id)
    {
        $this->populateRelation('division', $division);
        $this->cash_id = $cash_id;
        $this->company_customer_id = $company_customer_id;
        $this->discount = $discount;
        $this->paid = $paid;
        $this->payment_id = $payment_id;
        $this->sale_date = $sale_date;
        $this->staff_id = $staff_id;
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%warehouse_sale}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['division_id', 'required'],
            [['cash_id', 'company_customer_id', 'discount', 'division_id', 'payment_id', 'staff_id'], 'integer'],
            [['paid'], 'number'],
            [['sale_date'], 'safe'],
            [['cash_id'], 'exist', 'skipOnError' => false, 'targetClass' => CompanyCash::className(), 'targetAttribute' => ['cash_id' => 'id']],
            [['company_customer_id'], 'exist', 'skipOnError' => false, 'targetClass' => CompanyCustomer::className(), 'targetAttribute' => ['company_customer_id' => 'id']],
            [['payment_id'], 'exist', 'skipOnError' => false, 'targetClass' => Payment::className(), 'targetAttribute' => ['payment_id' => 'id']],
            [['staff_id'], 'exist', 'skipOnError' => false, 'targetClass' => Staff::className(), 'targetAttribute' => ['staff_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'cash_id' => Yii::t('app', 'Cash ID'),
            'company_customer_id' => Yii::t('app', 'Customer'),
            'discount' => Yii::t('app', 'Discount'),
            'division_id' => Yii::t('app', 'Division ID'),
            'paid' => Yii::t('app', 'Paid'),
            'payment_id' => Yii::t('app', 'Payment'),
            'staff_id' => Yii::t('app', 'Staff ID'),
            'sale_date' => Yii::t('app', 'Sale date'),
        ];
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
    public function getCashflow()
    {
        return $this->hasOne(CompanyCashflow::class, ['id' => 'cashflow_id'])
            ->viaTable('{{%company_cashflow_sales}}', ['sale_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPayment()
    {
        return $this->hasOne(Payment::className(), ['id' => 'payment_id']);
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
    public function getSaleProducts()
    {
        return $this->hasMany(SaleProduct::className(), ['sale_id' => 'id']);
    }

    /**
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            $related = $this->getRelatedRecords();
            /** @var Company $company */
            if (isset($related['company']) && $company = $related['company']) {
                $company->save();
                $this->company_id = $company->id;
            }
            if (isset($related['division']) && $division = $related['division']) {
                $division->save();
                $this->division_id = $division->id;
            }
            return true;
        }
        return false;
    }

    /**
     * @param SaleProduct[] $saleProducts
     * @return array
     */
    public static function getSaleData($saleProducts) {
        $subTotal = 0;
        $totalTax = 0;
        $totalSum = 0;
        foreach ($saleProducts as $key => $saleItem) {
            $tax = 0;
            $vat = $saleItem->product->vat ?? 0;
            $total = $saleItem->getFinalPrice();
            if (is_numeric($vat) && $vat != 0) {
                $tax += round($total/$vat, 2);
            }
            $totalTax += $tax;
            $subTotal += $total - $tax;
            $totalSum += $total;
        }
        return [
            'tax' => number_format($totalTax, 2, '.', ''),
            'subtotal' => number_format($subTotal, 2, '.', ''),
            'total' => number_format($totalSum, 2, '.', '')];
    }

    /**
     * @return float|int
     */
    public function getTotalCost() {
        $total = 0;
        foreach ($this->saleProducts as $key => $saleItem) {
            $total += $saleItem->getFinalPrice();
        }
        return $total;
    }

    /**
     * @inheritdoc
     */
    public static function find()
    {
        return new SaleQuery(get_called_class());
    }
}
