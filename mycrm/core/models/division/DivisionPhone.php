<?php

namespace core\models\division;

use Yii;

/**
 * This is the model class for table "{{%division_phones}}".
 *
 * @property integer $id
 * @property string $value
 * @property integer $division_id
 *
 * @property Division $division
 */
class DivisionPhone extends \yii\db\ActiveRecord
{
    /**
     * @const
     */
    const STATUS_DISABLED = 0;
    const STATUS_ENABLED = 1;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%division_phones}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['value'], 'required'],
            [['division_id'], 'integer'],
            [['value'], 'string', 'max' => 255],
            [['division_id'], 'exist', 'skipOnError' => true, 'targetClass' => Division::className(), 'targetAttribute' => ['division_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'value' => Yii::t('app', 'Phone Value'),
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
     *
     */
    public function enable()
    {
        $this->status = self::STATUS_ENABLED;
    }

    /**
     *
     */
    public function disable()
    {
        $this->status = self::STATUS_DISABLED;
    }

    /**
     * @param $division_id
     * @param $value
     * @return DivisionPhone
     */
    public static function add($division_id, $value)
    {
        $phone = new self();
        $phone->division_id = $division_id;
        $phone->value = $value;
        return $phone;
    }

    /**
     * @return array
     */
    public function fields()
    {
        return [
            'value'
        ];
    }
}
