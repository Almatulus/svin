<?php

namespace core\models\medCard;

use Yii;

/**
 * This is the model class for table "{{%med_card_diagnose_classes}}".
 *
 * @property integer            $id
 * @property string             $name
 * @property integer            $parent_id
 * @property string             $code
 *
 * @property MedCardDiagnosis[] $diagnoses
 */
class MedCardDiagnoseClass extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%med_card_diagnose_classes}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'code'], 'required'],
            [['parent_id'], 'integer'],
            [['name', 'code'], 'string', 'max' => 255],
            [['code'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'name' => Yii::t('app', 'Name'),
            'parent_id' => Yii::t('app', 'Parent ID'),
            'code' => Yii::t('app', 'Code'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDiagnoses()
    {
        return $this->hasMany(MedCardDiagnosis::className(), ['class_id' => 'id']);
    }
}
