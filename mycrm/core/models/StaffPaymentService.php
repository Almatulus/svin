<?php

namespace core\models;

use core\models\finance\Payroll;
use core\models\order\OrderService;
use Yii;

/**
 * This is the model class for table "{{%staff_payment_services}}".
 *
 * @property int $staff_payment_id
 * @property int $order_service_id
 * @property int $payroll_id
 * @property int $percent
 * @property int $sum
 *
 * @property OrderService $orderService
 * @property Payroll $payroll
 * @property StaffPayment $staffPayment
 */
class StaffPaymentService extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%staff_payment_services}}';
    }

    /**
     * @inheritdoc
     * @return \core\models\query\StaffPaymentServiceQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \core\models\query\StaffPaymentServiceQuery(get_called_class());
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['staff_payment_id', 'order_service_id', 'payroll_id', 'percent', 'sum'], 'required'],
            [['staff_payment_id', 'order_service_id', 'payroll_id', 'percent', 'sum'], 'default', 'value' => null],
            [['staff_payment_id', 'order_service_id', 'payroll_id', 'percent', 'sum'], 'integer'],
            [
                ['staff_payment_id', 'order_service_id'],
                'unique',
                'targetAttribute' => ['staff_payment_id', 'order_service_id']
            ],
            [
                ['order_service_id'],
                'exist',
                'skipOnError'     => true,
                'targetClass'     => OrderService::className(),
                'targetAttribute' => ['order_service_id' => 'id']
            ],
            [
                ['payroll_id'],
                'exist',
                'skipOnError'     => true,
                'targetClass'     => Payroll::className(),
                'targetAttribute' => ['payroll_id' => 'id']
            ],
            [
                ['staff_payment_id'],
                'exist',
                'skipOnError'     => true,
                'targetClass'     => StaffPayment::className(),
                'targetAttribute' => ['staff_payment_id' => 'id']
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'staff_payment_id' => Yii::t('app', 'Staff Payment ID'),
            'order_service_id' => Yii::t('app', 'Order Service ID'),
            'payroll_id'       => Yii::t('app', 'Payroll Scheme'),
            'percent'          => Yii::t('app', 'Percent'),
            'sum'              => Yii::t('app', 'Sum'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrderService()
    {
        return $this->hasOne(OrderService::className(), ['id' => 'order_service_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPayroll()
    {
        return $this->hasOne(Payroll::className(), ['id' => 'payroll_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStaffPayment()
    {
        return $this->hasOne(StaffPayment::className(), ['id' => 'staff_payment_id']);
    }
}
