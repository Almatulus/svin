<?php

namespace core\models;

use common\components\HistoryBehavior;
use core\helpers\ScheduleTemplateHelper;
use core\models\division\Division;
use core\models\user\User;
use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%schedule_templates}}".
 *
 * @property integer $id
 * @property integer $staff_id
 * @property integer $division_id
 * @property integer $interval_type
 * @property integer $type
 * @property string $created_at
 * @property string $updated_at
 * @property integer $created_by
 * @property integer $updated_by
 *
 * @property Division $division
 * @property ScheduleTemplateInterval[] $intervals
 * @property Staff $staff
 * @property User $createdBy
 * @property User $updatedBy
 */
class ScheduleTemplate extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%schedule_templates}}';
    }

    /**
     * @inheritdoc
     * @return \core\models\query\ScheduleTemplateQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \core\models\query\ScheduleTemplateQuery(get_called_class());
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [
                [
                    'staff_id',
                    'division_id',
                    'interval_type',
                    'type',
                ],
                'required'
            ],
            [['staff_id', 'division_id', 'interval_type', 'type', 'created_by', 'updated_by'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [
                ['division_id'],
                'exist',
                'skipOnError'     => true,
                'targetClass'     => Division::className(),
                'targetAttribute' => ['division_id' => 'id']
            ],
            [
                ['staff_id'],
                'exist',
                'skipOnError'     => true,
                'targetClass'     => Staff::className(),
                'targetAttribute' => ['staff_id' => 'id']
            ],
            [
                ['created_by'],
                'exist',
                'skipOnError'     => true,
                'targetClass'     => User::className(),
                'targetAttribute' => ['created_by' => 'id']
            ],
            [
                ['updated_by'],
                'exist',
                'skipOnError'     => true,
                'targetClass'     => User::className(),
                'targetAttribute' => ['updated_by' => 'id']
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'            => Yii::t('app', 'ID'),
            'staff_id'      => Yii::t('app', 'Staff ID'),
            'division_id'   => Yii::t('app', 'Division ID'),
            'interval_type' => Yii::t('app', 'Period'),
            'type'          => Yii::t('app', 'Type'),
            'created_at'    => Yii::t('app', 'Created at'),
            'updated_at'    => Yii::t('app', 'Updated at'),
            'created_by'    => Yii::t('app', 'Created by'),
            'updated_by'    => Yii::t('app', 'Updated by'),
        ];
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'value' => date("Y-m-d H:i:s")
            ],
            BlameableBehavior::class,
            HistoryBehavior::class
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDivision()
    {
        return $this->hasOne(Division::className(), ['id' => 'division_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getIntervals()
    {
        return $this->hasMany(ScheduleTemplateInterval::className(), ['schedule_template_id' => 'id']);
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
    public function getCreator()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUpdater()
    {
        return $this->hasOne(User::className(), ['id' => 'updated_by']);
    }

    /**
     * @return string
     */
    public function getPeriod()
    {
        return ScheduleTemplateHelper::periodValue($this->interval_type);
    }

    /**
     * @return array
     */
    public function extraFields()
    {
        return [
            'division',
            'intervals',
            'staff'
        ];
    }
}
