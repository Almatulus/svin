<?php

namespace core\models;

use Yii;

/**
 * This is the model class for table "{{%schedule_template_intervals}}".
 *
 * @property integer $schedule_template_id
 * @property integer $day
 * @property string $start
 * @property string $end
 * @property string $break_start
 * @property string $break_end
 */
class ScheduleTemplateInterval extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%schedule_template_intervals}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['schedule_template_id', 'day'], 'required'],
            [['schedule_template_id', 'day'], 'integer'],
            [['start', 'end', 'break_start', 'break_end'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'schedule_template_id' => Yii::t('app', 'Schedule Template ID'),
            'day'                  => Yii::t('app', 'Day'),
            'start'                => Yii::t('app', 'Start'),
            'end'                  => Yii::t('app', 'End'),
            'break_start'          => Yii::t('app', 'Break Start'),
            'break_end'            => Yii::t('app', 'Break End'),
        ];
    }
}
