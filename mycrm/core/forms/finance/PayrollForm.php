<?php

namespace core\forms\finance;

use core\models\finance\Payroll;
use core\services\dto\PayrollData;
use Yii;
use yii\base\Model;

class PayrollForm extends Model
{
    public $company_id;
    public $is_count_discount;
    public $name;
    public $service_mode;
    public $service_value;

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            ['company_id', 'default', 'value' => \Yii::$app->user->identity->company_id],
            [['company_id', 'is_count_discount', 'name', 'service_mode', 'service_value'], 'required'],
            [['company_id', 'service_mode', 'service_value'], 'integer'],
            [['is_count_discount'], 'boolean'],
            [['service_value'], 'integer', 'min' => 0],
            [['name'], 'string', 'max' => 255]

        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'                => Yii::t('app','ID'),
            'name'              => Yii::t('app','Name'),
            'service_value'     => Yii::t('app','Percent from services'),
            'service_mode'      => Yii::t('app','Mode'),
            'is_count_discount' => Yii::t('app','Is Count Discount'),
            'company_id'        => Yii::t('app','Company ID'),
        ];
    }

    /**
     * @return string
     */
    public function formName()
    {
        return 'Payroll';
    }

    /**
     * @return PayrollData
     */
    public function getDto()
    {
        return new PayrollData($this->company_id, $this->is_count_discount, $this->name, 0,
            Payroll::PERIOD_MONTH, $this->service_mode, $this->service_value);
    }
}
