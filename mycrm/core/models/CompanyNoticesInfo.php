<?php

namespace core\models;

use core\models\company\Company;
use Yii;

/**
 * This is the model class for table "crm_company_notices_info".
 *
 * @property integer $id
 * @property integer $company_id
 * @property integer $email_count
 * @property integer $email_limit
 * @property integer $push_count
 * @property integer $push_limit
 * @property integer $sms_count
 * @property integer $sms_limit
 *
 * @property Company $company
 */
class CompanyNoticesInfo extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'crm_company_notices_info';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['company_id'], 'required'],
            [['company_id', 'email_count', 'email_limit', 'push_count', 'push_limit', 'sms_count', 'sms_limit'], 'integer'],
            [['company_id'], 'exist', 'skipOnError' => false, 'targetClass' => Company::className(), 'targetAttribute' => ['company_id' => 'id']],
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
            'email_count' => Yii::t('app', 'Email Count'),
            'email_limit' => Yii::t('app', 'Email Limit'),
            'push_count' => Yii::t('app', 'Push Count'),
            'push_limit' => Yii::t('app', 'Push Limit'),
            'sms_count' => Yii::t('app', 'Sms Count'),
            'sms_limit' => Yii::t('app', 'Sms Limit'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCompany()
    {
        return $this->hasOne(Company::className(), ['id' => 'company_id']);
    }
}
