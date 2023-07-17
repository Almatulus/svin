<?php

namespace core\models\finance;

use core\models\Staff;
use core\models\finance\query\PayrollStaffQuery;
use Yii;

/**
 * This is the model class for table "{{%staff_payrolls}}".
 *
 * @property integer $id
 * @property integer $staff_id
 * @property integer $payroll_id
 * @property string $started_time
 * @property string $created_time
 *
 * @property Payroll $payroll
 * @property Staff $staff
 */
class PayrollStaff extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%staff_payrolls}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['staff_id', 'started_time'], 'required'],
            [['staff_id', 'payroll_id'], 'integer'],
            [['started_time', 'created_time'], 'safe'],
            [['staff_id', 'started_time'], 'unique', 'targetAttribute' => ['staff_id', 'started_time']],
            [['payroll_id'], 'exist', 'skipOnError' => true, 'targetClass' => Payroll::className(), 'targetAttribute' => ['payroll_id' => 'id']],
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
            'payroll_id' => Yii::t('app', 'Payroll ID'),
            'started_time' => Yii::t('app', 'Started Time'),
            'created_time' => Yii::t('app', 'Created Time'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPayroll()
    {
        return $this->hasOne(Payroll::className(), ['id' => 'payroll_id']);
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
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert))
        {
            if ($this->isNewRecord)
            {
                $this->created_time = (new \DateTime())->format("Y-m-d H:i:s");
            }
            return true;
        }
        return false;
    }

    /**
     * @inheritdoc
     */
    public static function find()
    {
        return new PayrollStaffQuery(get_called_class());
    }
}
