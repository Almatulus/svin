<?php

namespace core\models\medCard;

use core\models\ServiceCategory;
use Yii;

/**
 * This is the model class for table "{{%med_card_diagnoses}}".
 *
 * @property integer              $id
 * @property string               $name
 * @property string               $code
 * @property integer              $class_id
 *
 * @property MedCardComment[]     $commentTemplates
 * @property ServiceCategory[]    $serviceCategories
 * @property MedCardDiagnoseClass $class
 */
class MedCardDiagnosis extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%med_card_diagnoses}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'code', 'class_id'], 'required'],
            [['class_id'], 'integer'],
            [['name', 'code'], 'string', 'max' => 255],
            [['code'], 'unique'],
            [['class_id'], 'exist', 'skipOnError' => true, 'targetClass' => MedCardDiagnoseClass::className(), 'targetAttribute' => ['class_id' => 'id']],
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
            'code' => Yii::t('app', 'Code'),
            'class_id' => Yii::t('app', 'Class ID'),
        ];
    }

    public function fields()
    {
        return [
            'id',
            'name',
            'code',
            'class_id',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getClass()
    {
        return $this->hasOne(MedCardDiagnoseClass::className(), ['id' => 'class_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCommentTemplates()
    {
        return $this->hasMany(MedCardComment::className(), ['id' => 'diagnosis_id'])
            ->viaTable(
                '{{%med_card_comment_diagnosis_map}}',
                ['comment_template_id' => 'id']
            );
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getServiceCategories()
    {
        return $this->hasMany(ServiceCategory::className(), ['id' => 'service_category_id'])
            ->viaTable(
                '{{%med_card_diagnosis_service_category_map}}',
                ['med_card_diagnosis_id' => 'id']
            );
    }
}
