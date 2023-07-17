<?php

namespace core\models\customer;

use core\models\customer\query\CustomerContactQuery;
use Yii;

/**
 * This is the model class for table "{{%customer_contacts}}".
 *
 * @property integer $customer_id
 * @property integer $contact_id
 *
 * @property Customer $customer
 * @property Customer $contact
 */
class CustomerContact extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%customer_contacts}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['customer_id', 'contact_id'], 'required'],
            [['customer_id', 'contact_id'], 'integer'],
            [
                ['customer_id'],
                'exist',
                'skipOnError'     => true,
                'targetClass'     => Customer::className(),
                'targetAttribute' => ['customer_id' => 'id']
            ],
            [
                ['contact_id'],
                'exist',
                'skipOnError'     => true,
                'targetClass'     => Customer::className(),
                'targetAttribute' => ['contact_id' => 'id']
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'customer_id' => Yii::t('app', 'Customer ID'),
            'contact_id'  => Yii::t('app', 'Contact ID'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCustomer()
    {
        return $this->hasOne(Customer::className(), ['id' => 'customer_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getContact()
    {
        return $this->hasOne(Customer::className(), ['id' => 'contact_id']);
    }

    /**
     * @inheritdoc
     * @return CustomerContactQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new CustomerContactQuery(get_called_class());
    }

    /**
     * @return array
     */
    public function extraFields()
    {
        return [
            'contact',
            'customer'
        ];
    }
}
