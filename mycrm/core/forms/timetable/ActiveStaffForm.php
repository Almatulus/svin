<?php

namespace core\forms\timetable;

use core\models\company\CompanyPosition;
use core\models\division\Division;
use yii\base\Model;

/**
 * Class ActiveStaffForm
 * @package core\forms\timetable
 */
class ActiveStaffForm extends Model
{
    /**
     * @var \DateTime
     */
    public $date;
    /**
     * @var integer
     */
    public $division_id;
    /**
     * @var integer
     */
    public $position_id;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            ['date', 'required'],
            ['date', 'date'],
            ['date', 'filter', 'filter' => function ($val) {
                return \DateTime::createFromFormat('d.m.Y', $val);
            }],

            ['division_id', 'integer'],
            ['division_id', 'exist', 'targetClass' => Division::class, 'targetAttribute' => 'id'],

            ['position_id', 'integer'],
            ['position_id', 'exist', 'targetClass' => CompanyPosition::class, 'targetAttribute' => 'id'],
        ];
    }
}