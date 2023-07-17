<?php

namespace core\forms\staff;

use core\helpers\DateHelper;
use core\helpers\ScheduleTemplateHelper;
use core\models\division\Division;
use core\models\division\query\DivisionQuery;
use core\models\ScheduleTemplate;
use Yii;
use yii\base\Model;

/**
 * Class ScheduleTemplateForm
 * @package core\forms\staff
 *
 * @property integer $division_id
 * @property string $end
 * @property string $start
 * @property integer $type
 * @property array $intervals
 */
class ScheduleTemplateForm extends Model
{
    private $company_id;

    public $division_id;
    public $interval_type;
    public $start;
    public $type;

    public $intervals;

    /** @var ScheduleTemplate */
    public $template;

    /**
     *
     */
    public function init()
    {
        $this->start = date("Y-m-d");
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
                    return $query->company($this->company_id)->permitted();
                }
            ],

            ['interval_type', 'required'],
            ['interval_type', 'integer'],
            ['interval_type', 'in', 'range' => array_keys(ScheduleTemplateHelper::periods())],

            ['start', 'required'],
            ['start', 'date', 'format' => 'Y-m-d'],

            ['type', 'required'],
            ['type', 'integer'],
            ['type', 'in', 'range' => array_keys(ScheduleTemplateHelper::types())],

            ['intervals', 'required'],
            ['intervals', 'validateIntervals'],
            [
                'intervals',
                'filter',
                'filter'      => function ($intervals) {
                    if (is_array($intervals)) {
                        return array_filter($intervals, function ($intervalData) {
                            return isset($intervalData['is_enabled']) && $intervalData['is_enabled'];
                        });
                    }
                    return $intervals;
                },
                'skipOnError' => true
            ]
        ];
    }

    /**
     * @param $attribute
     */
    public function validateIntervals($attribute)
    {
        foreach ($this->{$attribute} as $day => $intervalData) {
            if (isset($intervalData['is_enabled']) && $intervalData['is_enabled'] != false) {
                $intervalForm = new ScheduleTemplateIntervalForm($this->type);
                $intervalForm->setAttributes([
                    'day'         => $day,
                    'start'       => $intervalData['start'] ?? null,
                    'end'         => $intervalData['end'] ?? null,
                    'break_start' => $intervalData['break_start'] ?? null,
                    'break_end'   => $intervalData['break_end'] ?? null,
                ]);
                if (!$intervalForm->validate()) {
                    foreach ($intervalForm->firstErrors as $attributeName => $message) {
                        $this->addError("{$attribute}[$day][$attributeName]", $message);
                    }
                }
            }
        }
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'division_id'   => Yii::t('app', 'Division'),
            'interval_type' => Yii::t('app', 'Period'),
            'staff_id'      => Yii::t('app', 'Staff'),
            'start'         => Yii::t('app', 'Start date'),
            'type'          => Yii::t('app', 'Type'),
            'intervals'     => Yii::t('app', 'Intervals'),
        ];
    }

    /**
     * @return string
     */
    public function formName()
    {
        return "";
    }

    /**
     * @param ScheduleTemplate $template
     */
    public function setTemplate(ScheduleTemplate $template)
    {
        $this->template = $template;
        $this->attributes = $template->attributes;

        foreach ($template->intervals as $interval) {
            $this->intervals[$interval->day] = $interval->attributes;
        }
    }

    /**
     * @param mixed $company_id
     */
    public function setCompanyId($company_id)
    {
        $this->company_id = $company_id;
    }
}

class ScheduleTemplateIntervalForm extends Model
{
    public $day;
    public $start;
    public $end;
    public $break_start;
    public $break_end;
    private $type;

    /**
     * ScheduleTemplateIntervalForm constructor.
     * @param int $type
     * @param array $config
     * @throws \InvalidArgumentException
     */
    public function __construct(int $type, array $config = [])
    {
        if (!in_array($type, array_keys(ScheduleTemplateHelper::types()))) {
            throw new \InvalidArgumentException("Invalid Type");
        }

        $this->type = $type;

        parent::__construct($config);
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            ['day', 'required'],
            ['day', 'integer'],

            ['start', 'required'],
            ['start', 'match', 'pattern' => DateHelper::HOURS_FULL_PATTERN],

            ['end', 'required'],
            ['end', 'match', 'pattern' => DateHelper::HOURS_FULL_PATTERN],
            ['end', 'compare', 'compareAttribute' => 'start', 'operator' => '>'],

            [
                'break_start',
                'required',
                'when' => function () {
                    return !empty($this->break_end);
                }
            ],
            ['break_start', 'match', 'pattern' => DateHelper::HOURS_FULL_PATTERN],
            ['break_start', 'compare', 'compareAttribute' => 'start', 'operator' => '>'],
            ['break_start', 'compare', 'compareAttribute' => 'end', 'operator' => '<'],

            [
                'break_end',
                'required',
                'when' => function () {
                    return !empty($this->break_start);
                }
            ],
            ['break_end', 'match', 'pattern' => DateHelper::HOURS_FULL_PATTERN],
            ['break_end', 'compare', 'compareAttribute' => 'start', 'operator' => '>'],
            ['break_end', 'compare', 'compareAttribute' => 'break_start', 'operator' => '>'],
            ['break_end', 'compare', 'compareAttribute' => 'end', 'operator' => '<'],
        ];
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'start'       => Yii::t('app', 'Working Start'),
            'end'         => Yii::t('app', 'Working Finish'),
            'break_start' => Yii::t('app', 'Break Start'),
            'break_end'   => Yii::t('app', 'Break End'),
        ];
    }
}