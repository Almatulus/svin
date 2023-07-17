<?php

namespace core\forms\staff;

use core\models\Staff;
use core\models\division\DivisionService;
use DateTime;
use Yii;
use yii\base\Model;

/**
 * @property integer $division_service_id
 * @property string $start_time
 * @property string $finish_time
 * @property string $format
 *
 * @property Staff|null $_staff
 */
class ScheduleForm extends Model
{
    public $division_service_id;
    public $start_time;
    public $finish_time;
    public $format;

    private $_staff = null;
    private $_divisionService = false;

    private $_formats = [
        'full' => 'Y-m-d H:i:s',
        'time' => 'H:i:s',
        'date' => 'Y-m-d',
    ];

    /**
     * Custom constructor
     * @param Staff $staff
     * @param array $config
     */
    public function __construct(Staff $staff, array $config = [])
    {
        $this->_staff = $staff;
        $this->format = 'full';
        parent::__construct($config);
    }

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['division_service_id', 'start_time', 'finish_time'], 'required'],

            [['division_service_id'], 'integer'],

            [['start_time', 'finish_time'], 'datetime', 'format' => 'php:Y-m-d H:i:s'],

            [['format'], 'string'],
            [['format'], 'in', 'range' => array_keys($this->_formats)],

            [['division_service_id'], 'exist', 'skipOnError' => false,
                'targetClass' => DivisionService::className(), 'targetAttribute' => ['division_service_id' => 'id']],

            ['division_service_id', 'validateStaffService'],
        ];
    }

    /**
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validateStaffService($attribute, $params)
    {
        $hasDivisionService = $this->_staff
                ->getDivisionServices()
                ->andWhere(['{{%division_services}}.id' => $this->division_service_id])
                ->exists();
        if (!$hasDivisionService) {
            $this->addError($attribute, Yii::t('app', 'Incorrect Division service'));
        }
    }

    /**
     * Returns schedule
     * @return array
     */
    public function getSchedule()
    {
        $start_date = new \DateTime($this->start_time);
        if ($start_date < (new \DateTime())) {
            $start_date = new \DateTime();
            $start_date->setTime(intval(date("G")) + 1, 0);
        }
        $finish_date = new \DateTime($this->finish_time);

        $listOfTime = $this->_staff->getAvailableSchedule($this->getDivisionService(), $start_date, $finish_date);

        $filter = [];
        foreach ($listOfTime as $time) {
            $filter[] = (new DateTime($time))->format($this->_formats[$this->format]);
        }
        $filter = array_filter($filter, function ($v, $k) use ($filter) {
            return $filter[$k + 1] != $v;
        }, ARRAY_FILTER_USE_BOTH);

        $result = [];
        foreach ($filter as $key => $item) {
            $result[] = $item;
        }
        return $result;
    }

    /**
     * Returns Division service model
     * @return DivisionService|null
     */
    private function getDivisionService()
    {
        if ($this->_divisionService == false) {
            $this->_divisionService = DivisionService::findOne($this->division_service_id);
        }
        return $this->_divisionService;
    }
}
