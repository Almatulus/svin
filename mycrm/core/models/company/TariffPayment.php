<?php

namespace core\models\company;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "{{%company_tariff_payments}}".
 *
 * @property integer $id
 * @property integer $sum
 * @property integer $company_id
 * @property integer $period
 * @property string $start_date
 * @property string $created_at
 * @property string $nextPaymentDate
 *
 * @property Company $company
 */
class TariffPayment extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%company_tariff_payments}}';
    }

    /**
     * @inheritdoc
     * @return \core\models\company\query\TariffPaymentQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \core\models\company\query\TariffPaymentQuery(get_called_class());
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['sum', 'company_id', 'period', 'start_date', 'created_at'], 'required'],
            [['sum', 'company_id', 'period'], 'integer'],
            [['start_date', 'created_at'], 'safe'],
            [
                ['company_id'],
                'exist',
                'skipOnError'     => true,
                'targetClass'     => Company::className(),
                'targetAttribute' => ['company_id' => 'id']
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'              => Yii::t('app', 'ID'),
            'sum'             => Yii::t('app', 'Sum'),
            'company_id'      => Yii::t('app', 'Company ID'),
            'period'          => Yii::t('app', 'Period'),
            'start_date'      => Yii::t('app', 'Payment Date'),
            'created_at'      => Yii::t('app', 'Created at'),
            'nextPaymentDate' => Yii::t('app', 'Next Payment')
        ];
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            [
                'class'      => TimestampBehavior::class,
                'attributes' => [
                    self::EVENT_BEFORE_INSERT => 'created_at',
                    self::EVENT_BEFORE_UPDATE => 'created_at',
                ],
                'value'      => new Expression("NOW()")
            ]
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCompany()
    {
        return $this->hasOne(Company::className(), ['id' => 'company_id']);
    }

    /**
     * @return string
     */
    public function getNextPaymentDate()
    {
        return (new \DateTime($this->start_date))->modify("+ {$this->getInterval()}")->format("Y-m-d");
    }

    /**
     * @return int|string
     */
    public function getInterval()
    {
        return $this->period . ' months';
    }
}
