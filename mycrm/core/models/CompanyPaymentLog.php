<?php

namespace core\models;

use common\components\HistoryBehavior;
use core\models\company\Company;
use core\models\company\Notification;
use core\models\customer\CustomerRequest;
use Yii;

/**
 * This is the model class for table "{{%company_payment_log}}".
 *
 * @property integer $id
 * @property integer $company_id
 * @property integer $value
 * @property integer $currency
 * @property string $code
 * @property string $created_time
 * @property string $confirmed_time
 * @property string $description
 * @property string $message
 * @property integer $customer_request_id
 *
 * @property Company $company
 */
class CompanyPaymentLog extends \yii\db\ActiveRecord
{
    const CURRENCY_KZT = 398;

    /**
     * @param integer $company_id
     * @param integer $currency
     * @param string  $description
     * @param string  $message
     * @param integer $value
     * @param bool    $is_confirmed
     *
     * @return CompanyPaymentLog
     * @throws \yii\base\Exception
     */
    public static function add($company_id, $currency, $description, $message, $value, bool $is_confirmed, $customer_request_id)
    {
        $model = new self();
        $model->company_id = $company_id;
        $model->currency = $currency;
        $model->description = $description;
        $model->message = $message;
        $model->value = $value;
        $model->created_time = (new \DateTime())->format("Y-m-d H:i:s");
        $model->code = Yii::$app->security->generateRandomString();
        if ($is_confirmed) {
            $model->confirmed_time = (new \DateTime())->format("Y-m-d H:i:s");
        }
        $model->customer_request_id = $customer_request_id;
        return $model;
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%company_payment_log}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['company_id', 'currency', 'code', 'created_time'], 'required'],
            [['company_id', 'value', 'currency'], 'integer'],
            [['created_time', 'confirmed_time', 'customer_request_id'], 'safe'],
            [['description', 'message'], 'string'],
            [['code'], 'string', 'max' => 255],
            [['company_id'], 'exist', 'skipOnError' => true, 'targetClass' => Company::className(), 'targetAttribute' => ['company_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'company_id' => Yii::t('app', 'Company ID'),
            'value' => Yii::t('app', 'Value'),
            'currency' => Yii::t('app', 'Currency'),
            'code' => Yii::t('app', 'Code'),
            'created_time' => Yii::t('app', 'Created Time'),
            'confirmed_time' => Yii::t('app', 'Confirmed Time'),
            'description' => Yii::t('app', 'Description'),
            'message' => Yii::t('app', 'Message'),
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
    public function getCustomerRequest()
    {
        return $this->hasOne(CustomerRequest::class, ['id' => 'customer_request_id']);
    }

    /**
     * Set payment confirmed
     */
    public function setConfirmed()
    {
        $this->confirmed_time = (new \DateTime())->format("Y-m-d H:i:s");
    }

    /**
     * @return string
     */
    public function getStatusLabel()
    {
        $message = $this->confirmed_time == null ? "Not approved" : "Approved";
        return Yii::t('app', $message);
    }

    /**
     * @param bool $insert
     * @param array $changedAttributes
     */
    public function afterSave($insert, $changedAttributes)
    {
        if ($this->isIncome()
            && $this->company->hasEnoughBalance(100)
        ) {
            Notification::deleteAll([
                'company_id' => $this->id,
                'type'       => Notification::TYPE_MIN_BALANCE,
            ]);
        }
    }

    /**
     * @return bool
     */
    public function isExpense()
    {
        return $this->value <= 0;
    }

    /**
     * @return bool
     */
    public function isIncome()
    {
        return $this->value > 0;
    }

    /**
     * @inheritdoc
     */
    public function fields()
    {
        return [
            'id'             => 'id',
            'value'          => 'value',
            'currency'       => 'currency',
            'code'           => 'code',
            'created_time'   => 'created_time',
            'confirmed_time' => 'confirmed_time',
            'description'    => 'description',
            'message'        => 'message',
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            HistoryBehavior::className(),
        ];
    }

    public function isApproved(){
        return $this->confirmed_time !== null;
    }
}
