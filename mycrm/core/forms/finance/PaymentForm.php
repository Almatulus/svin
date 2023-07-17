<?php

namespace core\forms\finance;

use core\models\StaffPayment;
use Yii;
use yii\base\Model;

/**
 * @TODO Refactor. Move code to ../forms folder
 * PaymentForm is for Financial Report
 * @package core\forms\finance
 *
 * @property integer $from
 * @property integer $to
 * @property integer $staff
 */
class PaymentForm extends Model
{
    public $from;
    public $to;
    public $staff = null;

    public function init()
    {
        $this->to   = date("Y-m-d");
        $this->from = date("Y-m-d", strtotime($this->to . " -1 months + 1 day"));
    }

    /**
     * @property integer $from
     * @property integer $to
     * @property integer $difference
     */
    public function rules()
    {
        return [
            [['from', 'to'], 'safe'],
            [['staff'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'from' => Yii::t('app', 'From'),
            'to' => Yii::t('app', 'To'),
            'staff' => Yii::t('app', 'Staff')
        ];
    }

    /**
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getPayments() {
        $query = StaffPayment::find()->where(':from <= created_at AND created_at < :to', [
            ':from' => $this->from,
            ":to" => $this->to
        ]);
        if ($this->staff) {
            $query->andFilterWhere(['staff_id' => $this->staff]);
        }
        return $query->all();
    }

}
