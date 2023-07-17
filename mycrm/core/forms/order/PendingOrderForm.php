<?php

namespace core\forms\order;

use core\helpers\customer\CustomerHelper;
use Yii;
use yii\base\Model;

/**
 * @property string $customer_name
 * @property string $customer_phone
 * @property string $note
 * @property string $color
 * @property string $date
 * @property integer $company_customer_id
 * @property integer $hours_before
 * @property integer $staff_id
 * @property integer $division_id
 */
class PendingOrderForm extends Model
{
    public $company_customer_id;
    public $customer_name;
    public $customer_phone;
    public $note;
    public $date;
    public $staff_id;
    public $division_id;

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['customer_name', 'date', 'staff_id', 'division_id'], 'required'],
            [['customer_name', 'customer_phone', 'note'], 'string'],

            ['customer_name', 'filter', 'filter' => function ($name) { return ucwords(trim($name)); }],
            ['customer_phone', 'match', 'pattern' => CustomerHelper::PHONE_VALIDATE_PATTERN],

            ['date', 'date', 'format' => 'php:Y-m-d'],

            [['staff_id', 'division_id', 'company_customer_id'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'customer_name' => Yii::t('app', 'Full Name'),
            'customer_phone' => Yii::t('app', 'Customer Phone'),
            'date' => Yii::t('app', 'Date'),
            'staff_id' => Yii::t('app', 'Staff ID'),
            'division_id' => Yii::t('app', 'Division ID'),
            'note' => Yii::t('app', 'Note'),
        ];
    }

    public function formName()
    {
        return 'PendingOrder';
    }
}
