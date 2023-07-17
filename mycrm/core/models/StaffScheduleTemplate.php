<?php

namespace core\models;

use Yii;

/**
 * This is the model class for table "{{%staff_schedule_templates}}".
 *
 * @property integer $id
 * @property integer $staff_id
 * @property string $date
 * @property integer $start_time
 * @property integer $finish_time
 * @property integer $status
 *
 * @property Staff $staff
 */
class StaffScheduleTemplate extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%staff_schedule_templates}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['staff_id', 'date', 'start_time', 'finish_time', 'status'], 'required'],
            [['staff_id', 'start_time', 'finish_time', 'status'], 'integer'],
            [['date'], 'safe'],
            [['staff_id'], 'exist', 'skipOnError' => true, 'targetClass' => Staff::className(), 'targetAttribute' => ['staff_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'staff_id' => Yii::t('app', 'Staff ID'),
            'date' => Yii::t('app', 'Date'),
            'start_time' => Yii::t('app', 'Start Time'),
            'finish_time' => Yii::t('app', 'Finish Time'),
            'status' => Yii::t('app', 'Status'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStaff()
    {
        return $this->hasOne(Staff::className(), ['id' => 'staff_id']);
    }
}
