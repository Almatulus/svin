<?php

namespace core\forms;

use core\models\division\Division;
use Yii;
use yii\base\Model;

/**
 * @property integer $start_date
 * @property integer $end_date
 */
class ScheduleFilterForm extends Model
{
    public $start_date;
    public $end_date;
    public $division_id;

    public function __construct(array $config = [])
    {
        $this->start_date = (new \DateTime())->format('Y-m-d');
        $this->end_date = (new \DateTime())->modify('+2 month')->format('Y-m-d');
        parent::__construct($config);
    }

    /**
     * @return \DateTime
     */
    public function getStartDate()
    {
        return new \DateTime($this->start_date);
    }

    /**
     * @return \DateTime
     */
    public function getEndDate()
    {
        return new \DateTime($this->end_date);
    }

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['division_id'], 'integer'],
            [['start_date', 'end_date'], 'date', 'format' => 'php:Y-m-d'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'start_date' => Yii::t('app', 'From'),
            'end_date' => Yii::t('app', 'To'),
        ];
    }

    public function formName()
    {
        return '';
    }
}
