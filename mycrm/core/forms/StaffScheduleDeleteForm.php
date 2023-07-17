<?php

namespace core\forms;

use core\models\division\Division;
use core\models\division\query\DivisionQuery;
use core\models\query\StaffQuery;
use core\models\Staff;
use yii\base\Model;

/**
 * @property string $date;
 * @property integer $staff_id
 * @property integer $division_id
 */
class StaffScheduleDeleteForm extends Model
{
    public $date;
    public $staff_id;
    public $division_id;

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['date', 'staff_id', 'division_id'], 'required'],
            [['staff_id', 'division_id'], 'integer'],
            ['date', 'date', 'format' => 'php:Y-m-d'],
            [
                ['division_id'],
                'exist',
                'skipOnError'     => true,
                'targetClass'     => Division::className(),
                'targetAttribute' => ['division_id' => 'id'],
                'filter'          => function (DivisionQuery $query) {
                    return $query->company()->permitted();
                }
            ],
            [
                ['staff_id'],
                'exist',
                'skipOnError'     => true,
                'targetClass'     => Staff::className(),
                'targetAttribute' => ['staff_id' => 'id'],
                'filter'          => function (StaffQuery $query) {
                    return $query->company()->permitted();
                }
            ],
        ];
    }

    public function formName()
    {
        return '';
    }
}
