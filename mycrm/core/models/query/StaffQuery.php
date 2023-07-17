<?php

namespace core\models\query;
use common\components\traits\DivisionTrait;
use core\models\Staff;
use DateTime;
use yii\db\ActiveQuery;

class StaffQuery extends ActiveQuery
{
    use DivisionTrait;

    /**
     * Filter by own company
     * @param bool $eagerLoading
     * @param int|null $company_id
     * @return StaffQuery
     */
    public function company($eagerLoading = true, int $company_id = null)
    {
        if (!$company_id) {
            $company_id = \Yii::$app->user->identity->company_id;
        }
        return $this->joinWith('divisions', $eagerLoading)
            ->andWhere(['{{%divisions}}.company_id' => $company_id]);
    }

    /**
     * Filter by enabled staff
     * @return StaffQuery
     */
    public function enabled()
    {
        return $this->andWhere(['{{%staffs}}.status' => Staff::STATUS_ENABLED]);
    }

    /**
     * Filter by disabled staff
     * @return StaffQuery
     */
    public function disabled()
    {
        return $this->andWhere(['{{%staffs}}.status' => Staff::STATUS_FIRED]);
    }

    /**
     * Filter by visibility in timetable
     * @return StaffQuery
     */
    public function timetableVisible()
    {
        return $this->andWhere(['{{%staffs}}.has_calendar' => 1]);
    }

    /**
     * Filter by division
     *
     * @param integer|array $division_id
     *
     * @param bool $eagerLoading
     * @return StaffQuery
     */
    public function division($division_id, bool $eagerLoading = true)
    {
        return $this->joinWith('divisions', $eagerLoading)
                    ->andFilterWhere([
                        '{{%staff_division_map}}.division_id' => $division_id
                    ]);
    }

    /**
     * Filter by having division service
     * @param integer $division_service_id
     * @return StaffQuery
     */
    public function divisionService($division_service_id)
    {
        $this->joinWith(['divisionServices']);
        return $this->andWhere(['{{%division_services}}.id' => $division_service_id]);
    }

    /**
     * Filter by scheme existence
     * @param integer $scheme_id
     * @return StaffQuery
     */
    public function noScheme($scheme_id = null) {
        /* @var $staffs Staff[] */
        if($scheme_id)
            return $this->andWhere([
                'OR',
                ['scheme_id' => null],
                ['scheme_id' => $scheme_id]
            ]);
        else
            return $this->andWhere([
                'scheme_id' => null
            ]);
    }

    /**
     * Whether staff has schedule for date
     * @param DateTime $date
     * @return StaffQuery
     */
    public function withSchedule(DateTime $date) {
        return $this->joinWith('staffSchedules schedule', true)
            ->andWhere(':from_date <= schedule.start_at AND schedule.start_at <= :to_date', [
                ':from_date' => $date->format('Y-m-d 00:00:00'),
                ':to_date' => $date->format('Y-m-d 24:00:00')
            ])
            ->andWhere(':from_date <= schedule.end_at AND schedule.end_at <= :to_date', [
                ':from_date' => $date->format('Y-m-d 00:00:00'),
                ':to_date' => $date->format('Y-m-d 24:00:00')
            ])
            ->groupBy(['{{%staffs}}.id']);
    }

    /**
     * Filters company staff
     * @return StaffQuery
     */
    public function valid() {
        return $this->enabled()->timetableVisible();
    }

    /**
     * @param int $id
     * @return $this
     */
    public function byId(int $id)
    {
        return $this->andWhere(['{{%staffs}}.id' => $id]);
    }

    /**
     * @param array $ids
     * @return $this
     */
    public function byIds(array $ids)
    {
        return $this->andWhere(['{{%staffs}}.id' => $ids]);
    }

    /**
     * @return string
     */
    public function getDivisionAttribute()
    {
        return "{{%staff_division_map}}.division_id";
    }

    /**
     * @return $this
     */
    public function permitted()
    {
        $divisions = \Yii::$app->user->identity->getPermittedDivisions();
        if ($divisions) {
            return $this->division($divisions);
        }
        return $this;
    }
}