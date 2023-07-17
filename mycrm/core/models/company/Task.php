<?php

namespace core\models\company;

use Yii;

/**
 * This is the model class for table "{{%company_tasks}}".
 *
 * @property int $id
 * @property int $type
 * @property string $comments
 * @property string $start_date
 * @property string $due_date
 * @property string $end_date
 * @property int $company_id
 *
 * @property Company $company
 */
class Task extends \yii\db\ActiveRecord
{
    const TYPE_QUALITY_CONTROL = 1;
    const TYPE_CONNECT_CLIENT = 2;
    const TYPE_MEETING = 3;
    const TYPE_PRESENTATION = 4;
    const TYPE_PAYMENT = 5;
    const TYPE_DECISION = 6;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%company_tasks}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type', 'company_id'], 'required'],
            [['type', 'company_id'], 'default', 'value' => null],
            [['type', 'company_id'], 'integer'],
            [['comments'], 'string'],
            [['start_date', 'due_date', 'end_date'], 'date', 'format' => 'php:Y-m-d H:i'],
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
            'id'         => Yii::t('app', 'ID'),
            'type'       => Yii::t('app', 'Type'),
            'comments'   => Yii::t('app', 'Comments'),
            'start_date' => Yii::t('app', 'Start Date'),
            'due_date'   => Yii::t('app', 'Due Date'),
            'end_date'   => Yii::t('app', 'End Date'),
            'company_id' => Yii::t('app', 'Company ID'),
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
     * Completes task
     */
    public function complete()
    {
        $this->end_date = date("Y-m-d H:i:s");
    }

    /**
     * @return bool
     */
    public function isCompleted()
    {
        return !is_null($this->end_date);
    }

    /**
     * @return string|null
     */
    public function getTypeName()
    {
        return self::getTypes()[$this->type] ?? null;
    }

    /**
     * @return array
     */
    public static function getTypes()
    {
        return [
            self::TYPE_QUALITY_CONTROL => 'Контроль клиента',
            self::TYPE_CONNECT_CLIENT  => 'Связаться с клиентом',
            self::TYPE_MEETING         => 'Встреча',
            self::TYPE_PRESENTATION    => 'Презентация',
            self::TYPE_PAYMENT         => 'Оплата за систему',
            self::TYPE_DECISION        => 'Решение'
        ];
    }

}
