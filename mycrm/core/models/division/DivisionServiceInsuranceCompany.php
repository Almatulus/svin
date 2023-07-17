<?php

namespace core\models\division;

use core\models\InsuranceCompany;
use Yii;

/**
 * This is the model class for table "crm_division_service_insurance_companies".
 *
 * @property int $id
 * @property int $division_service_id
 * @property int $insurance_company_id
 * @property int $price
 * @property int $price_max
 *
 * @property DivisionService $divisionService
 * @property InsuranceCompany $insuranceCompany
 */
class DivisionServiceInsuranceCompany extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'crm_division_service_insurance_companies';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['insurance_company_id', 'price'], 'required'],
            [['division_service_id', 'insurance_company_id', 'price', 'price_max'], 'default', 'value' => null],
            [['division_service_id', 'insurance_company_id', 'price', 'price_max'], 'integer'],
            [['division_service_id'], 'exist', 'skipOnError' => true, 'targetClass' => DivisionService::className(), 'targetAttribute' => ['division_service_id' => 'id']],
            [['insurance_company_id'], 'exist', 'skipOnError' => true, 'targetClass' => InsuranceCompany::className(), 'targetAttribute' => ['insurance_company_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'division_service_id' => Yii::t('app', 'Division Service'),
            'insurance_company_id' => Yii::t('app', 'Insurance Company'),
            'price' => Yii::t('app', 'Price'),
            'price_max' => Yii::t('app', 'Max Price currency'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDivisionService()
    {
        return $this->hasOne(DivisionService::className(), ['id' => 'division_service_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getInsuranceCompany()
    {
        return $this->hasOne(InsuranceCompany::className(), ['id' => 'insurance_company_id']);
    }
}
