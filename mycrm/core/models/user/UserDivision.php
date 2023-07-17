<?php

namespace core\models\user;

use core\models\division\Division;
use core\models\Staff;
use lhs\Yii2SaveRelationsBehavior\SaveRelationsBehavior;
use Yii;

/**
 * @TODO Going to remove this. Check whether model is not redundant
 * This is the model class for table "crm_user_divisions".
 *
 * @property integer  $id
 * @property integer  $staff_id
 * @property integer  $division_id
 *
 * @property Division $division
 * @property Staff    $staff
 * @property User     $user
 */
class UserDivision extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%user_divisions}}';
    }

    public static function add(Division $division, Staff $staff)
    {
        $model           = new UserDivision();
        $model->division = $division;
        $model->staff    = $staff;

        return $model;
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
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id'])
                    ->via('staff');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStaff()
    {
        return $this->hasOne(Staff::className(), ['id' => 'staff_id']);
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'saveRelations' => [
                'class'     => SaveRelationsBehavior::className(),
                'relations' => ['staff', 'division'],
            ],
        ];
    }

    public function transactions()
    {
        return [
            self::SCENARIO_DEFAULT => self::OP_ALL,
        ];
    }
}
