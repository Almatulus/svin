<?php

namespace core\models\medCard;

use core\models\company\Company;
use core\helpers\medCard\MedCardToothHelper;
use Yii;

/**
 * This is the model class for table "{{%med_card_teeth_diagnoses}}".
 *
 * @property integer $id
 * @property integer $company_id
 * @property string  $name
 * @property string  $abbreviation
 * @property string  $color
 *
 * @property Company $company
 * @property MedCardTooth[] $medCardTeeth
 */
class MedCardToothDiagnosis extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%med_card_teeth_diagnoses}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'abbreviation', 'color'], 'required'],
            [['name', 'abbreviation', 'color'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'           => Yii::t('app', 'ID'),
            'company_id'   => Yii::t('app', 'Company ID'),
            'name'         => Yii::t('app', 'Name'),
            'abbreviation' => Yii::t('app', 'Abbreviation'),
            'color'        => Yii::t('app', 'Color'),
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
     * @return \yii\db\ActiveQuery
     */
    public function getMedCardTeeth()
    {
        return $this->hasMany(MedCardTooth::className(), ['teeth_diagnosis_id' => 'id']);
    }

    /**
     * @param Company $company
     */
    public static function generateDefault(Company $company)
    {
        $defaultDiagnoses     = MedCardToothHelper::getDiagnoses();
        $defaultAbbreviations = MedCardToothHelper::getDiagnosisAbbreviations();
        $defaultColors        = MedCardToothHelper::getDiagnosisColors();

        foreach ($defaultDiagnoses as $diagnosis_id => $diagnosis) {

            $abbreviation
                = isset($defaultAbbreviations[$diagnosis_id])
                ? $defaultAbbreviations[$diagnosis_id] : '';
            $color
                = isset($defaultColors[$diagnosis_id])
                ? $defaultColors[$diagnosis_id] : '#FFF';

            $model               = new MedCardToothDiagnosis();
            $model->company_id   = $company->id;
            $model->name         = $diagnosis;
            $model->abbreviation = $abbreviation;
            $model->color        = $color;
            $model->insert(false);
        }
    }
}
