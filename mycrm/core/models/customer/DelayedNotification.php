<?php

namespace core\models\customer;

use core\models\division\DivisionService;
use Yii;
use yii\db\Expression;

/**
 * This is the model class for table "{{%customer_delayed_notifications}}".
 *
 * @property integer $id
 * @property integer $company_customer_id
 * @property string $date
 * @property integer $division_service_id
 * @property string $interval
 * @property integer $status
 * @property string $created_at
 * @property string $executed_at
 *
 * @property CompanyCustomer $companyCustomer
 * @property DivisionService $divisionService
 */
class DelayedNotification extends \yii\db\ActiveRecord
{
    const STATUS_NEW = 1;
    const STATUS_CANCELED = 2;
    const STATUS_EXECUTED = 3;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%delayed_notifications_queue}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['company_customer_id', 'date', 'division_service_id', 'interval', 'created_at'], 'required'],
            [['company_customer_id', 'division_service_id', 'status'], 'integer'],
            [['date', 'created_at', 'executed_at'], 'safe'],
            [['interval'], 'string', 'max' => 255],
            [['status'], 'in', 'range' => [self::STATUS_NEW, self::STATUS_CANCELED, self::STATUS_EXECUTED]],
            [
                ['company_customer_id'],
                'exist',
                'skipOnError'     => true,
                'targetClass'     => CompanyCustomer::className(),
                'targetAttribute' => ['company_customer_id' => 'id']
            ],
            [
                ['division_service_id'],
                'exist',
                'skipOnError'     => true,
                'targetClass'     => DivisionService::className(),
                'targetAttribute' => ['division_service_id' => 'id']
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'                  => Yii::t('app', 'ID'),
            'company_customer_id' => Yii::t('app', 'Company Customer ID'),
            'date'                => Yii::t('app', 'Date'),
            'division_service_id' => Yii::t('app', 'Division Service ID'),
            'interval'            => Yii::t('app', 'Interval'),
            'status'              => Yii::t('app', 'Status'),
            'created_at'          => Yii::t('app', 'Created At'),
            'executed_at'         => Yii::t('app', 'Executed At'),
        ];
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            [
                'class'      => \yii\behaviors\TimestampBehavior::className(),
                'attributes' => [
                    self::EVENT_BEFORE_INSERT => 'created_at',
                    self::EVENT_BEFORE_UPDATE => 'created_at',
                ],
                'value'      => new Expression('NOW()')
            ]
        ];
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
    public function getDivisionService()
    {
        return $this->hasOne(DivisionService::className(), ['id' => 'division_service_id']);
    }

    /**
     * @return void
     */
    public function enable()
    {
        $this->status = self::STATUS_NEW;
    }

    /**
     * @return void
     */
    public function cancel()
    {
        $this->status = self::STATUS_CANCELED;
    }
}
