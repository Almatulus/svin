<?php

namespace core\forms\order;

use core\models\division\Division;
use core\models\Staff;
use yii\base\Model;

class OrderOverlapForm extends Model
{
    public $division_id;
    public $end;
    public $staff_id;
    public $start;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['end', 'start', 'division_id', 'staff_id'], 'required'],
            [['division_id', 'staff_id'], 'integer'],
            ['division_id', 'exist', 'targetClass' => Division::class, 'targetAttribute' => 'id'],
            ['staff_id', 'exist', 'targetClass' => Staff::class, 'targetAttribute' => 'id'],
            [['end', 'start'], 'date', 'format' => 'php:Y-m-d H:i'],
        ];
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'start'       => \Yii::t('app', 'Start'),
            'end'         => \Yii::t('app', 'End'),
            'division_id' => \Yii::t('app', 'Division'),
            'staff_id'    => \Yii::t('app', 'Staff'),
        ];
    }
}