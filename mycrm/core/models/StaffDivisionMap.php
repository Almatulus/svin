<?php

namespace core\models;

use core\models\division\Division;
use Yii;

/**
 * This is the model class for table "{{%staff_division_map}}".
 *
 * @property integer $staff_id
 * @property integer $division_id
 *
 * @property Division $division
 * @property Staff $staff
 */
class StaffDivisionMap extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%staff_division_map}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['staff_id', 'division_id'], 'required'],
            [['staff_id', 'division_id'], 'integer'],
            [
                ['staff_id', 'division_id'],
                'unique',
                'targetAttribute' => ['staff_id', 'division_id'],
                'message'         => 'The combination of Staff ID and Division ID has already been taken.'
            ],
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
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'staff_id'    => Yii::t('app', 'Staff ID'),
            'division_id' => Yii::t('app', 'Division ID'),
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
    public function getStaff()
    {
        return $this->hasOne(Staff::className(), ['id' => 'staff_id']);
    }
}
