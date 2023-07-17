<?php

namespace api\modules\v2\search\common;

use core\models\company\query\CompanyQuery;
use core\models\Staff;
use core\models\StaffSchedule;
use DateInterval;
use DatePeriod;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;

class ScheduleSearch extends StaffSchedule
{
    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['staff_id', 'division_id', 'start_at', 'end_at'], 'required'],
            [['staff_id', 'division_id'], 'integer'],
            [['start_at', 'end_at'], 'date', 'format' => 'php:Y-m-d'],
            ['end_at', 'validatePeriod'],
        ];
    }

    /**
     * Validates the requesting period.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validatePeriod($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $difference = $this->getStartDateTime()->diff($this->getEndDateTime());

            if ($difference->m > 1) {
                $this->addError($attribute, \Yii::t('app', 'Incorrect end date. You are allowed to get schedules for a month only'));
            }
        }
    }

    /**
     * @return array
     */
    public function fields()
    {
        return [
            'id',
            'division_id',
            'staff_id',
            'start_at',
            'end_at',
            'break_start',
            'break_end'
        ];
    }

    /**
     * @param $params
     *
     * @return mixed
     * @throws BadRequestHttpException
     * @throws NotFoundHttpException
     */
    public function search($params)
    {
        $this->load($params);

        if (!$this->validate()) {
            return $this;
        }

        /* @var Staff $staff */
        $staff = Staff::find()->enabled()->joinWith([
            'divisions.company' => function (CompanyQuery $query) {
                return $query->enabledIntegration();
            }
        ], false)->andWhere(['{{%staffs}}.id' => $this->staff_id])->one();

        if (!$staff) {
            throw new NotFoundHttpException();
        }

        $schedules = [];
        $interval = DateInterval::createFromDateString('1 day');
        $period = new DatePeriod($this->getStartDateTime(), $interval, $this->getEndDateTime());

        foreach ($period as $dt) {
            $schedule = $staff->getDateScheduleAt($this->division_id, $dt);
            if ($schedule) {
                $schedules[] = $schedule;
            }
        }

        return $schedules;
    }

    public function formName()
    {
        return '';
    }

    /**
     * @return \DateTime
     */
    private function getStartDateTime()
    {
        return new \DateTime($this->start_at);
    }

    /**
     * @return \DateTime
     */
    private function getEndDateTime()
    {
        return new \DateTime($this->end_at);
    }
}
