<?php

namespace core\models\customer;

use core\helpers\Security;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "{{%customer_subscriptions}}".
 *
 * @property integer $id
 * @property integer $company_customer_id
 * @property string $key
 * @property string $first_visit
 * @property integer $number_of_persons
 * @property string $start_date
 * @property string $end_date
 * @property integer $quantity
 * @property integer $status
 * @property double $price
 * @property integer $type
 * @property string $created_at
 * @property string $updated_at
 *
 * @property CompanyCustomer $companyCustomer
 */
class CustomerSubscription extends \yii\db\ActiveRecord
{
    const STATUS_NEW = 0;
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 2;

    const TYPE_VISITS = 1;
    const TYPE_TIME = 2;

    public $services_ids = [];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%customer_subscriptions}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['company_customer_id', 'key', 'start_date', 'end_date', 'quantity', 'price', 'type'], 'required'],
            [['company_customer_id', 'number_of_persons', 'quantity', 'status', 'type'], 'integer'],
            [['first_visit', 'services_ids', 'start_date', 'end_date', 'created_at', 'updated_at'], 'safe'],
            [['price'], 'number', 'min' => 0],
            [['!key'], 'string', 'max' => 255],
            [['!key'], 'unique'],
            [['first_visit', 'start_date', 'end_date'], 'date', 'format' => "Y-m-d"],
            ['status', 'in', 'range' => [self::STATUS_NEW, self::STATUS_ENABLED, self::STATUS_DISABLED]],
            ['type', 'in', 'range' => [self::TYPE_TIME, self::TYPE_VISITS]],
            [['company_customer_id'], 'exist', 'skipOnError' => false, 'targetClass' => CompanyCustomer::className(), 'targetAttribute' => ['company_customer_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'company_customer_id' => Yii::t('app', 'Customer'),
            'key' => Yii::t('app', 'Season ticket number'),
            'first_visit' => Yii::t('app', 'First visit'),
            'number_of_persons' => Yii::t('app', 'Number of persons'),
            'start_date' => Yii::t('app', 'Purchased'),
            'end_date' => Yii::t('app', 'Expiry date'),
            'quantity' => Yii::t('app', 'Quantity'),
            'status' => Yii::t('app', 'Status'),
            'price' => Yii::t('app', 'Price'),
            'type' => Yii::t('app', 'Type'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'services_ids' => Yii::t('app', 'Services')
        ];
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
    public function init()
    {   
        if ($this->isNewRecord) {
            $maxId = self::find()->max('id') + 1;
            srand(time());
            $this->key = "M" . Yii::$app->user->identity->company_id 
                        . $maxId . strtoupper(Security::random_str(5));
        }
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
    public function getServices()
    {
        return $this->hasMany(CustomerSubscriptionService::className(), ['subscription_id' => 'id']);
    }

    /**
     * Gets types
     */
    public static function getTypes() 
    {
        return [
            self::TYPE_VISITS => 'По количеству визитов',
            self::TYPE_TIME => 'По времени'
        ];
    }

    /**
     * Gets statuses
     */
    public static function getStatuses()
    {
        return [
            self::STATUS_NEW => 'Не активирован',
            self::STATUS_ENABLED => 'Действующий',
            self::STATUS_DISABLED => 'Истекший',
        ];
    }

    public function getTypeLabel()
    {
        return self::getTypes()[$this->type];
    }

    public function getStatusLabel()
    {
        return self::getStatuses()[$this->status];
    }

    /**
     * @inheritdoc
     * @return \core\models\customer\query\CustomerSubscriptionQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \core\models\customer\query\CustomerSubscriptionQuery(get_called_class());
    }
}
