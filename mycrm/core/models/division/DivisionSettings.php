<?php

namespace core\models\division;

use Yii;

/**
 * This is the model class for table "{{%division_settings}}".
 *
 * @property int $id
 * @property int $division_id
 * @property string $notification_time_before_lunch
 * @property string $notification_time_after_lunch
 *
 * @property Division $division
 */
class DivisionSettings extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%division_settings}}';
    }

    /**
     * @param string $notification_time_before_lunch
     * @param string $notification_time_after_lunch
     * @return DivisionSettings
     */
    public static function add(
        string $notification_time_before_lunch = null,
        string $notification_time_after_lunch = null
    ) {
        $model = new self();
        $model->notification_time_before_lunch = $notification_time_before_lunch;
        $model->notification_time_after_lunch = $notification_time_after_lunch;
        return $model;
    }

    /**
     * @param string $notification_time_before_lunch
     * @param string $notification_time_after_lunch
     */
    public function edit(string $notification_time_before_lunch = null, string $notification_time_after_lunch = null)
    {
        $this->notification_time_before_lunch = $notification_time_before_lunch;
        $this->notification_time_after_lunch = $notification_time_after_lunch;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['division_id'], 'required'],
            [['division_id'], 'default', 'value' => null],
            [['division_id'], 'integer'],
            [
                ['division_id'],
                'exist',
                'skipOnError'     => true,
                'targetClass'     => Division::className(),
                'targetAttribute' => ['division_id' => 'id']
            ],
            ['division_id', 'unique'],
            [['notification_time_before_lunch', 'notification_time_after_lunch'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'                             => Yii::t('app', 'ID'),
            'division_id'                    => Yii::t('app', 'Division ID'),
            'notification_time_before_lunch' => Yii::t('app', 'Sms Notification Before Pivot'),
            'notification_time_after_lunch'  => Yii::t('app', 'Sms Notification After Pivot'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDivision()
    {
        return $this->hasOne(Division::className(), ['id' => 'division_id']);
    }
}
