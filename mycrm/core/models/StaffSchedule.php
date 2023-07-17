<?php

namespace core\models;

use core\helpers\order\OrderConstants;
use core\models\division\Division;
use core\models\order\Order;
use core\models\query\StaffScheduleQuery;
use core\repositories\exceptions\NotFoundException;
use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%staff_schedules}}".
 *
 * @property integer $id
 * @property integer $staff_id
 * @property integer $division_id
 * @property string $start_at
 * @property string $end_at
 * @property string $break_start
 * @property string $break_end
 * @property int $remainingTime
 *
 * @property Staff $staff
 * @property Division $division
 */
class StaffSchedule extends ActiveRecord
{
    /**
     * @param Staff $staff
     * @param Division $division
     * @param string $start_at
     * @param string $end_at
     * @param string $break_start
     * @param string $break_end
     * @return StaffSchedule
     */
    public static function add(
        Staff $staff,
        Division $division,
        $start_at,
        $end_at,
        $break_start,
        $break_end
    ) {
        self::guardScheduleNotExists($staff, $division, $start_at, $end_at);

        $model = new StaffSchedule();
        $model->populateRelation('staff', $staff);
        $model->populateRelation('division', $division);
        $model->start_at = $start_at;
        $model->end_at   = $end_at;
        $model->break_start = $break_start;
        $model->break_end = $break_end;
        $model->guardValidTime();

        return $model;
    }

    /**
     * @param string $start_at
     * @param string $end_at
     * @param string $break_start
     * @param string $break_end
     */
    public function edit($start_at, $end_at, $break_start, $break_end)
    {
        $this->start_at = $start_at;
        $this->end_at = $end_at;
        $this->break_start = $break_start;
        $this->break_end = $break_end;
        $this->guardValidTime();
    }

    /**
     * Checks whether there is no order in given period of time
     * @param \DateTime $start_time
     * @param \DateTime $end_time
     * @param Staff $staff
     * @return bool
     */
    public static function isTimeAvailable(\DateTime $start_time, \DateTime $end_time, Staff $staff)
    {
        self::guardScheduleExists(
            $staff,
            $start_time->format('Y-m-d H:i:s'),
            $end_time->format('Y-m-d H:i:s')
        );

        $sql = <<<SQL
((datetime + interval '1 min' * duration) > :start_at AND (datetime + interval '1 min' * duration) <= :end_at)
OR
(:end_at > datetime AND datetime >= :start_at)
OR
(:start_at <= datetime AND (datetime + interval '1 min' * duration) <= :end_at)
OR
(datetime <= :start_at AND :end_at <= (datetime + interval '1 min' * duration))
SQL;
        $orders_query = Order::find()
            ->where($sql, [
                ':start_at' => $start_time->format('Y-m-d H:i:s'),
                ':end_at' => $end_time->format('Y-m-d H:i:s'),
            ])
            ->andWhere(['status' => [OrderConstants::STATUS_ENABLED, OrderConstants::STATUS_FINISHED]])
            ->andWhere(['staff_id' => $staff->id]);

        return !$orders_query->exists();
    }

    /**
     * Returns staff schedule cell inner text
     * @return string
     */
    public function getScheduleTitle()
    {
        return Yii::$app->formatter->asTime($this->start_at) . "\n" . Yii::$app->formatter->asTime($this->end_at);
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%staff_schedules}}';
    }

    /**
     * @deprecated
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['staff_id', 'start_at', 'end_at', 'division_id'], 'required'],
            [['staff_id', 'division_id'], 'integer'],
            [['start_at', 'end_at', 'break_start', 'break_end'], 'safe'],
            [
                ['staff_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Staff::className(),
                'targetAttribute' => ['staff_id' => 'id']
            ],
            [
                ['division_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Division::className(),
                'targetAttribute' => ['division_id' => 'id']
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'          => Yii::t('app', 'ID'),
            'staff_id'    => Yii::t('app', 'Staff ID'),
            'start_at'    => Yii::t('app', 'Start At'),
            'end_at'      => Yii::t('app', 'End At'),
            'break_start' => Yii::t('app', 'Break Start'),
            'break_end'   => Yii::t('app', 'Break End'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStaff()
    {
        return $this->hasOne(Staff::className(), ['id' => 'staff_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDivision()
    {
        return $this->hasOne(Division::className(), ['id' => 'division_id']);
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {

            $related = $this->getRelatedRecords();
            /** @var Staff $staff */
            if (isset($related['staff']) && $staff = $related['staff']) {
                $staff->save();
                $this->staff_id = $staff->id;
            }
            /** @var Division $division */
            if (isset($related['division']) && $division = $related['division']) {
                $division->save();
                $this->division_id = $division->id;
            }

            return true;
        }
        return false;
    }

    /**
     * Check whether start_at is greater that end_at
     */
    private function guardValidTime()
    {
        $start_time = new \DateTime($this->start_at);
        $end_time = new \DateTime($this->end_at);
        if ($start_time >= $end_time) {
            throw new \RuntimeException('Wrong timing set');
        }
    }

    /**
     * Check whether schedule already exist
     *
     * @param Staff    $staff
     * @param Division $division
     * @param string   $start_at
     * @param string   $end_at
     */
    private static function guardScheduleNotExists(Staff $staff, Division $division, $start_at, $end_at)
    {
        $schedule = StaffSchedule::find()
            ->where('start_at <= :start_at AND :start_at < end_at', [':start_at' => $start_at])
            ->orWhere('start_at < :end_at AND :end_at <= end_at', [':end_at' => $end_at])
            ->andWhere([
                'staff_id' => $staff->id,
                'division_id' => $division->id,
            ])
            ->one();
        if ($schedule != null) {
            throw new \RuntimeException('Schedule already exist');
        }
    }

    /**
     * Check whether schedule already exist
     * @param Staff $staff
     * @param string $start_at
     * @param string $end_at
     */
    private static function guardScheduleExists(Staff $staff, $start_at, $end_at)
    {
        $schedule = StaffSchedule::find()
            ->where('start_at <= :start_at AND :start_at <= end_at')
            ->andWhere('start_at <= :end_at AND :end_at <= end_at')
            ->andWhere([
                'OR',
                ['break_start' => null],
                ['break_end' => null],
                'break_start >= :end_at AND break_start > :start_at',
                'break_end < :end_at AND break_end <= :start_at',
            ])
            ->andWhere(['staff_id' => $staff->id])
            ->params([
                ':start_at' => $start_at,
                ':end_at'   => $end_at
            ])
            ->exists();
        if (!$schedule) {
            throw new NotFoundException('Schedule not exist ' . $start_at . ' ' . $end_at);
        }
    }

    /**
     * @return array
     */
    public function extraFields()
    {
        return [
            'division',
            'staff'
        ];
    }

    /**
     * @return StaffScheduleQuery
     */
    public static function find()
    {
        return new StaffScheduleQuery(get_called_class());
    }

    /**
     * Time in seconds till the end of schedule
     * @return int
     */
    public function getRemainingTime()
    {
        return strtotime($this->end_at) - time();
    }
}
