<?php

namespace core\models\customer;

use core\models\division\DivisionService;
use Yii;

/**
 * This is the model class for table "{{%customer_subscription_services}}".
 *
 * @property integer $id
 * @property integer $subscription_id
 * @property integer $division_service_id
 *
 * @property CustomerSubscription $subscription
 * @property DivisionService $divisionService
 */
class CustomerSubscriptionService extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%customer_subscription_services}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['subscription_id', 'division_service_id'], 'required'],
            [['subscription_id', 'division_service_id'], 'integer'],
            [['subscription_id'], 'exist', 'skipOnError' => true, 'targetClass' => CustomerSubscription::className(), 'targetAttribute' => ['subscription_id' => 'id']],
            [['division_service_id'], 'exist', 'skipOnError' => true, 'targetClass' => DivisionService::className(), 'targetAttribute' => ['division_service_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'subscription_id' => Yii::t('app', 'Subscription ID'),
            'division_service_id' => Yii::t('app', 'Division Service ID'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSubscription()
    {
        return $this->hasOne(CustomerSubscription::className(), ['id' => 'subscription_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDivisionService()
    {
        return $this->hasOne(DivisionService::className(), ['id' => 'division_service_id']);
    }
}
