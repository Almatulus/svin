<?php

namespace core\models\medCard;

/**
 * This is the model class for table "{{%med_card_tabs}}".
 *
 * @property integer $id
 * @property integer $diagnosis_id
 * @property integer $med_card_id
 *
 * @property MedCard $medCard
 * @property MedCardDiagnosis $diagnosis
 * @property MedCardTabComment[] $comments
 * @property MedCardTabService[] $services
 * @property MedCardTooth[] $teeth
 */
class MedCardTab extends \yii\db\ActiveRecord
{
    /**
     * @param MedCard $medCard
     *
     * @param int|null $diagnosis_id
     * @return MedCardTab
     */
    public static function add(MedCard $medCard, int $diagnosis_id = null)
    {
        $model = new MedCardTab();
        $model->diagnosis_id = $diagnosis_id;
        $model->populateRelation('medCard', $medCard);

        return $model;
    }

    /**
     * Returns total price sum
     *
     * @return integer
     */
    public function getServicesTotalPrice()
    {
        return array_reduce($this->services, function ($total, MedCardTabService $model) {
            return $total + $model->getTotalPrice();
        }, 0);
    }

    /**
     * @inheritdoc
     */
    public function fields()
    {
        return [
            'id'           => 'id',
            'diagnosis_id' => 'diagnosis_id',
            'diagnosis',
            'med_card_id'  => 'med_card_id',
            'comments'     => 'comments',
            'teeth'        => 'teeth',
            'childTeeth'   => 'childTeeth',
            'services'     => 'services'
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%med_card_tabs}}';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getComments()
    {
        return $this->hasMany(MedCardTabComment::className(),
            ['med_card_tab_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTeeth()
    {
        return $this->hasMany(MedCardTooth::className(),
            ['med_card_tab_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMedCard()
    {
        return $this->hasOne(MedCard::className(), ['id' => 'med_card_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDiagnosis()
    {
        return $this->hasOne(MedCardDiagnosis::className(), ['id' => 'diagnosis_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getServices()
    {
        return $this->hasMany(MedCardTabService::className(),
            ['med_card_tab_id' => 'id'])
            ->andWhere(['deleted_time' => null]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAdultTeeth()
    {
        return $this->hasMany(MedCardTooth::className(),
            ['med_card_tab_id' => 'id'])
            ->where(['type' => MedCardTooth::TYPE_ADULT]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getChildTeeth()
    {
        return $this->hasMany(MedCardTooth::className(),
            ['med_card_tab_id' => 'id'])
            ->where(['type' => MedCardTooth::TYPE_CHILD]);
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            $related = $this->getRelatedRecords();
            /** @var MedCard $medCard */
            if (isset($related['medCard']) && $medCard = $related['medCard']) {
                $medCard->save();
                $this->med_card_id = $medCard->id;
            }

            return true;
        }

        return false;
    }
}
