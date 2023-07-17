<?php

namespace core\models\document;

use core\models\medCard\MedCardToothDiagnosis;
use Yii;

/**
 * This is the model class for table "{{%document_dental_card_elements}}".
 *
 * @property integer $document_id
 * @property integer $number
 * @property integer $diagnosis_id
 * @property integer $mobility
 *
 * @property Document $document
 * @property MedCardToothDiagnosis $diagnosis
 */
class DentalCardElement extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%document_dental_card_elements}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['document_id', 'number', 'diagnosis_id'], 'required'],
            [['document_id', 'number', 'diagnosis_id', 'mobility'], 'integer'],
            [
                ['document_id'],
                'exist',
                'skipOnError'     => true,
                'targetClass'     => Document::className(),
                'targetAttribute' => ['document_id' => 'id']
            ],
            [
                ['diagnosis_id'],
                'exist',
                'skipOnError'     => true,
                'targetClass'     => MedCardToothDiagnosis::className(),
                'targetAttribute' => ['diagnosis_id' => 'id']
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'document_id'  => Yii::t('app', 'Document ID'),
            'number'       => Yii::t('app', 'Tooth'),
            'diagnosis_id' => Yii::t('app', 'Diagnosis ID'),
            'mobility'     => Yii::t('app', 'Mobility'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDocument()
    {
        return $this->hasOne(Document::className(), ['id' => 'document_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDiagnosis()
    {
        return $this->hasOne(MedCardToothDiagnosis::className(), ['id' => 'diagnosis_id']);
    }

    /**
     * @return array
     */
    public function fields()
    {
        return [
            'diagnosis_id',
            'number',
            'mobility',
            'diagnosis'
        ];
    }

    /**
     * @return array
     */
    public function extraFields()
    {
        return [
            'document'
        ];
    }
}
