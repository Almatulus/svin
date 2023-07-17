<?php

namespace core\models\division;

use Yii;

/**
 * This is the model class for table "{{%division_schedule}}".
 *
 * @property integer $id
 * @property integer $division_id
 * @property integer $day_num
 * @property boolean $is_open
 * @property string $from
 * @property string $to
 *
 * @property Division $division
 */
class DivisionSchedule extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%division_schedule}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['division_id', 'day_num', 'from', 'to'], 'required'],
            [['division_id', 'day_num'], 'integer'],
            [['is_open'], 'boolean'],
            [['from', 'to'], 'safe'],
            [['division_id'], 'exist', 'skipOnError' => true, 'targetClass' => Division::className(), 'targetAttribute' => ['division_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'division_id' => 'Подразделение',
            'day_num' => 'День недели',
            'is_open' => 'Открыто',
            'from' => 'С',
            'to' => 'До',
        ];
    }

    /**
     * Sets default values to new instance
     * @inheritdoc
     */
    public function init()
    {
        if ($this->isNewRecord) {
            $hours = self::defaultHours();
            $this->from = $hours[0];
            $this->to = end($hours);
            $this->is_open = true;
        }
    }

    /**
     * Converts time attributes
     * @inheritdoc
     */
    public function afterFind()
    {
        $this->from = Yii::$app->formatter->asTime($this->from, 'short');
        $this->to = Yii::$app->formatter->asTime($this->to, 'short');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDivision()
    {
        return $this->hasOne(Division::className(), ['id' => 'division_id']);
    }

    /**
     * Gets time range
     * @return array
     */
    public static function defaultHours() {
        $hours = [];
        for ($i = 7; $i <= 23; $i++) {
            $hour = $i . ":00";
            if (intval($i / 10) == 0) {
                $hour = "0" . $i . ":00";
            }
            $hours[$hour] = $hour;
        }
        return $hours;
    }

    /**
     * Gets weekdays
     * @return array
     */
    public static function weekdays()
    {
        return [
            1 => Yii::t('app', 'Monday'),
            2 => Yii::t('app', 'Tuesday'),
            3 => Yii::t('app', 'Wednesday'),
            4 => Yii::t('app', 'Thursday'),
            5 => Yii::t('app', 'Friday'),
            6 => Yii::t('app', 'Saturday'),
            7 => Yii::t('app', 'Sunday'),
        ];
    }

    /**
     * Validates overlapping of division schedules
     * @param $models DivisionSchedule[]
     * @return bool
     */
    public static function validateOverlapping($models) {
        
        //iterate through all models
        foreach ($models as $key => $model) {

            $cloneModels = $models;
            unset($cloneModels[$key]);

            // index schedules by weekday
            $periods = \yii\helpers\ArrayHelper::index($cloneModels, null, 'day_num');

            if (isset($periods[$model->day_num])) {
                $ranges = $periods[$model->day_num];
                foreach ($ranges as $index => $range) {
                    if (self::isOverlap($model, $range)) {
                        $model->addError('from', 'Диапазоны пересекаются.');
                        return false;
                    }
                }
            }
        }

        return true;
    }

    /**
     * @param $model
     * @param $compareModel
     * @return bool
     */
    public static function isOverlap($model, $compareModel) {
        return (($model->from >= $compareModel->from && $model->from <= $compareModel->to) ||
                ($compareModel->from >= $model->from && $compareModel->from <= $model->to));
    }
}
