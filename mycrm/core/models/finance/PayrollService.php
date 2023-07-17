<?php

namespace core\models\finance;

use core\models\division\DivisionService;
use Yii;

/**
 * This is the model class for table "crm_payroll_services".
 *
 * @property integer $id
 * @property integer $division_service_id
 * @property integer $service_value
 * @property integer $service_mode
 * @property integer $scheme_id
 *
 * @property Payroll $scheme
 * @property DivisionService $service
 */
class PayrollService extends \yii\db\ActiveRecord
{

    public $category_id;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'crm_payroll_services';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['division_service_id', 'service_value'], 'required'],
            [['division_service_id', 'service_value', 'service_mode', 'scheme_id'], 'integer'],
            [['service_value'], 'integer', 'min' => 0],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'division_service_id' => Yii::t('app', 'Division Service ID'),
            'service_value' => Yii::t('app', 'Service Value'),
            'service_mode' => Yii::t('app', 'Service Mode'),
            'scheme_id' => Yii::t('app', 'Scheme ID'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getScheme()
    {
        return $this->hasOne(Payroll::className(), ['id' => 'scheme_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDivisionService()
    {
        return $this->hasOne(DivisionService::className(), ['id' => 'division_service_id']);
    }
}
