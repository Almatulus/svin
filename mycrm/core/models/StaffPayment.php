<?php

namespace core\models;

use core\models\finance\CompanyCashflow;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;


/**
 * This is the model class for table "{{%staff_payments}}".
 *
 * @property integer $id
 * @property string $start_date
 * @property string $end_date
 * @property string $payment_date
 * @property integer $staff_id
 * @property double $salary
 * @property string $created_at
 * @property string $updated_at
 *
 * @property CompanyCashflow $cashflow
 * @property Staff $staff
 * @property StaffPaymentService[] $services
 */
class StaffPayment extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%staff_payments}}';
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
            [['start_date', 'end_date', 'created_at', 'updated_at'], 'safe'],
            [['staff_id'], 'integer'],
            [['end_date', 'start_date', 'salary', 'staff_id'], 'required'],
            [['salary'], 'number'],
            [['end_date', 'start_date'], 'date', 'format' => "yyyy-MM-dd"],
            [['staff_id'], 'exist', 'skipOnError' => true, 'targetClass' => Staff::className(), 'targetAttribute' => ['staff_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'         => Yii::t('app', 'ID'),
            'start_date' => 'С',
            'end_date'   => 'По(включительно)',
            'staff_id'   => Yii::t('app', 'Staff ID'),
            'salary'     => 'Получено',
            'created_at' => 'Дата выдачи',
            'updated_at' => Yii::t('app', 'Updated At'),
            'payment_date' => 'Дата выдачи'
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCashflow()
    {
        return $this->hasOne(CompanyCashflow::className(), ['id' => 'cashflow_id'])
            ->viaTable('{{%company_cashflow_salaries}}', ['staff_payment_id' => 'id']);
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
    public function getServices()
    {
        return $this->hasMany(StaffPaymentService::class, ['staff_payment_id' => 'id']);
    }

    /**
     * @return bool
     */
    public function isEditable()
    {
        return !is_null($this->cashflow);
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->staff->getFullName() . ": {$this->start_date} - {$this->end_date}";
    }

    public static function create(string $start_date, string $end_date, string $payment_date, float $salary, int $staff_id)
    {
        $payment = new self();
        $payment->start_date = $start_date;
        $payment->end_date = $end_date;
        $payment->payment_date = $payment_date;
        $payment->salary = $salary;
        $payment->staff_id = $staff_id;

        return $payment;
    }
}
