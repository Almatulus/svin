<?php

namespace core\forms\finance;

use core\models\division\Division;
use core\models\division\query\DivisionQuery;
use core\models\finance\PayrollStaff;
use core\models\query\StaffQuery;
use core\models\Staff;
use Yii;
use yii\base\Model;

/**
 * Class SalaryForm
 * @package core\forms\finance
 *
 * @property string $payment_till
 * @property string $payment_from
 * @property double $salary
 * @property integer $staff_id
 * @property integer $division_id
 * @property Staff $staff
 * @property PayrollStaff[] $schemes
 */
class SalaryForm extends Model
{
    public $date_range;
    public $staff_id;
    public $division_id;

    public $payment_till;
    public $payment_from;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->payment_till = date('Y-m-01', strtotime("+ 1 month"));
        $this->payment_from = date("Y-m-d", strtotime($this->payment_till . " -1 months"));

        $this->date_range = implode(' - ', [
            $this->payment_from,
            $this->payment_till
        ]);
    }

    /**
     * @return bool
     */
    public function beforeValidate()
    {
        if ($this->date_range) {
            list($this->payment_from, $this->payment_till) = explode(' - ', $this->date_range);
            $this->payment_from = date('Y-m-d', strtotime($this->payment_from));
            $this->payment_till = date('Y-m-d', strtotime("{$this->payment_till}"));
        }

        return parent::beforeValidate();
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            ['division_id', 'required'],
            ['division_id', 'integer'],
            [
                'division_id',
                'exist',
                'targetClass'     => Division::class,
                'targetAttribute' => 'id',
                'filter'          => function (DivisionQuery $query) {
                    return $query->company()->permitted();
                }
            ],

            ['staff_id', 'required'],
            ['staff_id', 'integer'],
            [
                'staff_id',
                'exist',
                'targetClass'     => Staff::class,
                'targetAttribute' => 'id',
                'filter'          => function (StaffQuery $query) {
                    return $query->company(false)->permitted()->division($this->division_id);
                },
                'when'            => function () {
                    return $this->hasErrors('division_id');
                }
            ],

            ['date_range', 'required'],

            ['payment_from', 'required'],
            ['payment_from', 'date', 'format' => 'yyyy-MM-dd'],

            ['payment_till', 'required'],
            ['payment_till', 'date', 'format' => 'yyyy-MM-dd'],
            ['payment_from', 'validateDates'],
        ];
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'staff_id'     => Yii::t('app', 'Staff'),
            'division_id'  => Yii::t('app', 'Division'),
            'payment_from' => Yii::t('app', 'From'),
            'payment_till' => Yii::t('app', 'To'),
            'date_range'   => Yii::t('app', 'Range')
        ];
    }

    /**
     *
     */
    public function validateDates()
    {
        if (strtotime($this->payment_from) > strtotime($this->payment_till)) {
            $this->addError('payment_from',
                Yii::t('yii', '{attribute} must be greater than "{compareValueOrAttribute}".', [
                    'attribute'               => 'С',
                    'compareValueOrAttribute' => 'До'
                ]));
        }
    }

    /**
     * @return string
     */
    public function formName()
    {
        return '';
    }
}
