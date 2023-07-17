<?php

namespace core\forms;

use core\helpers\DateHelper;
use core\models\division\Division;
use core\models\division\query\DivisionQuery;
use core\models\query\StaffQuery;
use core\models\Staff;
use Yii;
use yii\base\Model;

/**
 * @property integer $staff_id;
 * @property integer $division_id;
 * @property string $date;
 * @property string $start;
 * @property string $end;
 * @property string $break_start;
 * @property string $break_end;
 */
class StaffScheduleCreateForm extends Model
{
    public $break_start;
    public $break_end;
    public $start;
    public $end;
    public $staff_id;
    public $division_id;
    public $date;

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['staff_id', 'date', 'division_id'], 'required'],
            ['date', 'date', 'format' => 'php:Y-m-d'],
            [['staff_id', 'division_id'], 'integer'],

            ['start', 'date', 'format' => 'php:H:i'],

            ['end', 'match', 'pattern' => DateHelper::HOURS_FULL_PATTERN],
            ['end', 'compare', 'compareAttribute' => 'start', 'operator' => '>'],

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
            [
                'break_start',
                'required',
                'when' => function () {
                    return !empty($this->break_end);
                }
            ],
            [
                'break_end',
                'required',
                'when' => function () {
                    return !empty($this->break_start);
                }
            ],
            ['break_start', 'date', 'format' => 'HH:mm'],
            ['break_end', 'date', 'format' => 'HH:mm'],
            [
                'break_end',
                'validateBreakEnd',
                'when' => function (self $form) {
                    return !$form->hasErrors('break_start') && !$form->hasErrors('break_end');
                }
            ],
        ];
    }

    /**
     * @param $attribute
     */
    public function validateBreakStart($attribute)
    {
        $start = (new \DateTime($this->date . " " . $this->start));
        $end = (new \DateTime($this->date . " " . $this->end));
        if ($this->end == '24:00') {
            $end->modify('+1 day');
        }
        $break_start = (new \DateTime($this->date . " " . $this->break_start));

        if ($start >= $break_start) {
            $errorMessage = Yii::t('yii', '{attribute} must be greater than "{compareValueOrAttribute}".', [
                'attribute'               => $this->getAttributeLabel('break_start'),
                'compareValueOrAttribute' => $this->getAttributeLabel('start')
            ]);
            $this->addError($attribute, $errorMessage);
        }

        if ($end <= $break_start) {
            $errorMessage = Yii::t('yii', '{attribute} must be greater than "{compareValueOrAttribute}".', [
                'attribute'               => $this->getAttributeLabel('end'),
                'compareValueOrAttribute' => $this->getAttributeLabel('break_start')
            ]);
            $this->addError($attribute, $errorMessage);
        }
    }

    /**
     * @param $attribute
     */
    public function validateBreakEnd($attribute)
    {
        $start = (new \DateTime($this->date . " " . $this->start));
        $end = (new \DateTime($this->date . " " . $this->end));
        if ($this->end == '24:00') {
            $end->modify('+1 day');
        }
        $break_start = (new \DateTime($this->date . " " . $this->break_start));
        $break_end = (new \DateTime($this->date . " " . $this->break_end));

        if ($start >= $break_end) {
            $errorMessage = Yii::t('yii', '{attribute} must be greater than "{compareValueOrAttribute}".', [
                'attribute'               => $this->getAttributeLabel('break_end'),
                'compareValueOrAttribute' => $this->getAttributeLabel('start')
            ]);
            $this->addError($attribute, $errorMessage);
        }

        if ($end <= $break_end) {
            $errorMessage = Yii::t('yii', '{attribute} must be greater than "{compareValueOrAttribute}".', [
                'attribute'               => $this->getAttributeLabel('end'),
                'compareValueOrAttribute' => $this->getAttributeLabel('break_end')
            ]);
            $this->addError($attribute, $errorMessage);
        }

        if ($break_start >= $break_end) {
            $errorMessage = Yii::t('yii', '{attribute} must be greater than "{compareValueOrAttribute}".', [
                'attribute'               => $this->getAttributeLabel('break_start'),
                'compareValueOrAttribute' => $this->getAttributeLabel('break_end')
            ]);
            $this->addError($attribute, $errorMessage);
        }
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'staff_id'    => Yii::t('app', 'Staff ID'),
            'start'       => Yii::t('app', 'Start At'),
            'end'         => Yii::t('app', 'End At'),
            'break_start' => Yii::t('app', 'Break Start'),
            'break_end'   => Yii::t('app', 'Break End'),
        ];
    }

    /**
     * @return string
     */
    public function formName()
    {
        return '';
    }
}
