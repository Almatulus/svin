<?php

namespace core\models\customer;

use core\models\division\Division;
use Yii;

/**
 * This is the model class for table "{{%customer_favourites}}".
 *
 * @property integer $id
 * @property integer $customer_id
 * @property integer $division_id
 *
 * @property Customer $customer
 * @property Division $division
 */
class CustomerFavourite extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%customer_favourites}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['customer_id', 'division_id'], 'required'],
            [['customer_id', 'division_id'], 'integer'],
            [['customer_id', 'division_id'], 'unique', 'targetAttribute' => ['customer_id', 'division_id'], 'message' => 'The combination of Customer ID and Division ID has already been taken.'],
            [['customer_id'], 'exist', 'skipOnError' => true, 'targetClass' => Customer::className(), 'targetAttribute' => ['customer_id' => 'id']],
            [['division_id'], 'exist', 'skipOnError' => true, 'targetClass' => Division::className(), 'targetAttribute' => ['division_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'customer_id' => Yii::t('app', 'Customer ID'),
            'division_id' => Yii::t('app', 'Division ID'),
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
    public function getDivision()
    {
        return $this->hasOne(Division::className(), ['id' => 'division_id']);
    }
}
