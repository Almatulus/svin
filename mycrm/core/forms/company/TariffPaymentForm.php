<?php

namespace core\forms\company;

use core\models\company\Company;
use yii\base\Model;

class TariffPaymentForm extends Model
{
    public $period;
    public $start_date;
    public $sum;

    public function rules()
    {
        return [
            ['sum', 'required'],
            ['sum', 'integer', 'min' => 1],

            ['period', 'required'],
            ['period', 'integer', 'min' => 1, 'max' => 12],

            ['start_date', 'required'],
            ['start_date', 'date', 'format' => 'yyyy-mm-dd']
        ];
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'sum'        => \Yii::t('app', 'Sum'),
            'period'     => \Yii::t('app', 'Period'),
            'start_date' => \Yii::t('app', 'Payment Date'),
        ];
    }
}