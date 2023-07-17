<?php

namespace core\models\medCard;

use Yii;

/**
 * This is the model class for table "{{%med_card_tooth}}".
 *
 * @property integer               $med_card_tab_id
 * @property integer               $teeth_num
 * @property integer               $mobility
 * @property integer               $type
 * @property integer               $teeth_diagnosis_id
 *
 * @property MedCardTab            $medCardTab
 * @property MedCardToothDiagnosis $medCardTabTeethDiagnosis
 */
class MedCardTooth extends \yii\db\ActiveRecord
{
    const TYPE_ADULT = 1;
    const TYPE_CHILD = 2;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%med_card_tooth}}';
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'teeth_num' => Yii::t('app', 'Teeth Num'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMedCardTab()
    {
        return $this->hasOne(MedCardTab::className(),
            ['id' => 'med_card_tab_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMedCardTabTeethDiagnosis()
    {
        return $this->hasOne(MedCardToothDiagnosis::className(),
            ['id' => 'teeth_diagnosis_id']);
    }

    /**
     * @param MedCardTab            $medCardTab
     * @param MedCardToothDiagnosis $medCardTeethDiagnosis
     * @param integer               $teeth_number
     * @param integer               $type
     * @param integer               $mobility
     *
     * @return MedCardTooth
     */
    public static function add(
        MedCardTab $medCardTab,
        MedCardToothDiagnosis $medCardTeethDiagnosis,
        int $teeth_number,
        int $type,
        int $mobility = null
    ): MedCardTooth {
        $model = new self();
        $model->populateRelation('medCardTab', $medCardTab);
        $model->populateRelation('medCardTabTeethDiagnosis',
            $medCardTeethDiagnosis);
        $model->teeth_num = $teeth_number;
        $model->type      = $type;
        $model->mobility  = $mobility;

        return $model;
    }


    /**
     * @param MedCardToothDiagnosis $medCardTeethDiagnosis
     * @param integer               $mobility
     */
    public function edit(
        MedCardToothDiagnosis $medCardTeethDiagnosis,
        int $mobility = null
    ) {
        $this->populateRelation('medCardTabTeethDiagnosis',
            $medCardTeethDiagnosis);
        $this->mobility = $mobility;
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            $related = $this->getRelatedRecords();
            /** @var MedCardTab $medCardTab */
            if (isset($related['medCardTab'])
                && $medCardTab = $related['medCardTab']
            ) {
                $medCardTab->save();
                $this->med_card_tab_id = $medCardTab->id;
            }

            /** @var MedCardToothDiagnosis $medCardTabTeethDiagnosis */
            if (isset($related['medCardTabTeethDiagnosis'])
                && $medCardTabTeethDiagnosis
                    = $related['medCardTabTeethDiagnosis']
            ) {
                $medCardTabTeethDiagnosis->save();
                $this->teeth_diagnosis_id = $medCardTabTeethDiagnosis->id;
            }

            return true;
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function fields()
    {
        return [
            'med_card_tab_id'        => 'med_card_tab_id',
            'mobility'               => 'mobility',
            'diagnosis_id'           => 'teeth_diagnosis_id',
            'teeth_num'              => 'teeth_num',
            'type'                   => 'type',
            'diagnosis_name'         => function (self $model) {
                return $model->medCardTabTeethDiagnosis->name;
            },
            'diagnosis_abbreviation' => function (self $model) {
                return $model->medCardTabTeethDiagnosis->abbreviation;
            },
            'diagnosis_color'        => function (self $model) {
                return $model->medCardTabTeethDiagnosis->color;
            },
            'datetime'           => function (self $model) {
                return $model->medCardTab->medCard->order->datetime;
            },
            'staff_id'               => function (self $model) {
                return $model->medCardTab->medCard->order->staff_id;
            },
            'staff_name'             => function (self $model) {
                return $model->medCardTab->medCard->order->staff->getFullName();
            },
        ];
    }
}
