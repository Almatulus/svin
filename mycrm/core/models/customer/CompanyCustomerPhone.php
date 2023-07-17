<?php

namespace core\models\customer;

use core\helpers\customer\CustomerHelper;
use Yii;

/**
 * This is the model class for table "{{%company_customer_phones}}".
 *
 * @property int $company_customer_id
 * @property string $phone
 *
 * @property CompanyCustomer $companyCustomer
 */
class CompanyCustomerPhone extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%company_customer_phones}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['company_customer_id', 'phone'], 'required'],
            [['company_customer_id'], 'default', 'value' => null],
            [['company_customer_id'], 'integer'],

            ['phone', 'string'],
            ['phone', 'match', 'pattern' => CustomerHelper::PHONE_VALIDATE_PATTERN],

            [['company_customer_id', 'phone'], 'unique', 'targetAttribute' => ['company_customer_id', 'phone']],
            [
                ['company_customer_id'],
                'exist',
                'skipOnError'     => true,
                'targetClass'     => CompanyCustomer::className(),
                'targetAttribute' => ['company_customer_id' => 'id']
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'company_customer_id' => Yii::t('app', 'Company Customer ID'),
            'phone'               => Yii::t('app', 'Phone'),
        ];
    }

    public function fields()
    {
        return [
            'value' => 'phone',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCompanyCustomer()
    {
        return $this->hasOne(CompanyCustomer::className(), ['id' => 'company_customer_id']);
    }
}
