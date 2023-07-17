<?php

namespace core\models\finance;

use core\models\company\Company;
use core\models\Staff;
use Yii;

/**
 * This is the model class for table "crm_payrolls".
 *
 * @property integer $id
 * @property string $name
 * @property integer $service_value
 * @property integer $service_mode
 * @property integer $salary
 * @property integer $salary_mode
 * @property boolean $is_count_discount
 * @property integer $company_id
 *
 * @property Company $company
 * @property PayrollService[] $indexedPayrollServices
 * @property PayrollService[] $payrollServices
 * @property PayrollStaff[] $payrollStaffs
 * @property Staff[] $staffs
 */
class Payroll extends \yii\db\ActiveRecord
{

    const PAYROLL_MODE_PERCENTAGE = 0;
    const PAYROLL_MODE_CURRENCY = 1;

    const PERIOD_HOUR = 0;
    const PERIOD_DAY = 1;
    const PERIOD_MONTH = 2;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%payrolls}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['company_id', 'is_count_discount', 'name', 'salary',
                'salary_mode', 'service_value', 'service_mode'], 'required'],
            [['service_value', 'service_mode', 'salary_mode', 'company_id'], 'integer'],
            [['is_count_discount'], 'boolean'],
            [['salary', 'service_value'], 'integer', 'min' => 0],
            [['name'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app','ID'),
            'name' => Yii::t('app','Name'),
            'service_value' => Yii::t('app','Services'),
            'service_mode' => Yii::t('app','Mode'),
            'salary' => Yii::t('app','Salary'),
            'salary_mode' => Yii::t('app','Mode'),
            'is_count_discount' => Yii::t('app','Is Count Discount'),
            'company_id' => Yii::t('app','Company ID'),
        ];
    }

    public static function getModeLabels()
    {
        return [
            self::PAYROLL_MODE_PERCENTAGE => Yii::t('app','%'),
            self::PAYROLL_MODE_CURRENCY => Yii::t('app','Currency'),
        ];
    }

    public static function getPeriodLabels()
    {
        return [
            self::PERIOD_MONTH => Yii::t('app','Month'),
            self::PERIOD_DAY => Yii::t('app','Day'),
            self::PERIOD_HOUR => Yii::t('app','Hour'),
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
    public function getStaffs()
    {
        return $this->hasMany(Staff::className(), ['id' => 'staff_id'])
            ->viaTable('crm_staff_payrolls', ['payroll_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPayrollServices()
    {
        return $this->hasMany(PayrollService::className(), ['scheme_id' => 'id']);
    }

    /**
     * Gets payrolls services indexed by division_service_id
     * @return \yii\db\ActiveQuery
     */
    public function getIndexedPayrollServices()
    {
        return $this->getPayrollServices()->indexBy('division_service_id');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPayrollStaffs()
    {
        return $this->hasMany(PayrollStaff::className(), ['payroll_id' => 'id']);
    }

    /**
     * Assign current user company to the Model
     * @return bool
     */
    public function beforeValidate()
    {
        if($this->isNewRecord && !$this->company_id && isset(Yii::$app->user->identity))
            $this->company_id = \Yii::$app->user->identity->company_id;
        return parent::beforeValidate();
    }

    /**
     * @inheritdoc
     */
    public function beforeDelete()
    {
        $this->unlinkAll("payrollStaffs", true);
        $this->unlinkAll("payrollServices", true);

        return parent::beforeDelete();
    }

    /**
     * Returns the money earned from the OrderService
     * @param int $division_service_id
     * @param int $price
     * @param int $discount
     * @return int
     */
    public function calculateServicePayment(int $division_service_id, int $price, int $discount)
    {
        $scheme = $this->indexedPayrollServices[$division_service_id] ?? $this;

        $value = $this->calculateFinalPrice($price, $discount);

        switch ($scheme->service_mode) {
            case Payroll::PAYROLL_MODE_CURRENCY:
                return $scheme->service_value;
                break;
            case Payroll::PAYROLL_MODE_PERCENTAGE:
                return ($scheme->service_value) / 100 * $value;
                break;
            default:
                return 0;
                break;
        }
    }

    /**
     * @param int $division_service_id
     * @return int
     */
    public function getServicePercent(int $division_service_id)
    {
        $scheme = $this->indexedPayrollServices[$division_service_id] ?? $this;
        return $scheme->service_value;
    }

    public function getPayrollScheme(int $division_service_id)
    {
        return $this->indexedPayrollServices[$division_service_id] ?? $this;
    }

    /**
     * @param int $price
     * @param int $discount
     * @return float|int
     */
    public function calculateFinalPrice(int $price, int $discount)
    {
        return $this->is_count_discount
            ? ($discount == 100 ? 0 : $price * (100 - $discount) / 100)
            : $price;
    }

    /**
     * @param $company_id
     * @param $is_count_discount
     * @param $name
     * @param $salary
     * @param $salary_mode
     * @param $service_mode
     * @param $service_value
     * @return Payroll
     */
    public static function add($company_id, $is_count_discount, $name, $salary,
                               $salary_mode, $service_mode, $service_value)
    {
        $model =  new Payroll();
        $model->company_id = $company_id;
        $model->is_count_discount = $is_count_discount;
        $model->name = $name;
        $model->salary = $salary;
        $model->salary_mode = $salary_mode;
        $model->service_mode = $service_mode;
        $model->service_value = $service_value;
        return $model;
    }

    /**
     * @param $company_id
     * @param $is_count_discount
     * @param $name
     * @param $salary
     * @param $salary_mode
     * @param $service_mode
     * @param $service_value
     */
    public function edit($company_id, $is_count_discount, $name, $salary,
                         $salary_mode, $service_mode, $service_value)
    {
        $this->company_id = $company_id;
        $this->is_count_discount = $is_count_discount;
        $this->name = $name;
        $this->salary = $salary;
        $this->salary_mode = $salary_mode;
        $this->service_mode = $service_mode;
        $this->service_value = $service_value;
    }
}
