<?php

use core\models\ServiceCategory;
use core\models\company\Company;
use core\helpers\medCard\MedCardToothHelper;
use core\models\medCard\MedCardToothDiagnosis;
use core\models\medCard\MedCardTooth;
use yii\db\Migration;

class m170901_184422_alter_tooth_diagnosis extends Migration
{
    public function safeUp()
    {
        $this->addColumn(
            '{{%med_card_tooth}}',
            'teeth_diagnosis_id',
            $this->integer()->unsigned()
        );

        $this->addColumn(
            '{{%med_card_tooth}}',
            'id',
            $this->primaryKey()
        );

        $this->delete('{{%med_card_tooth}}', ['diagnosis_id' => null]);

        $this->addForeignKey(
            'fk_med_card_tooth_teeth_diagnosis',
            '{{%med_card_tooth}}',
            'teeth_diagnosis_id',
            '{{%med_card_teeth_diagnoses}}',
            'id');

        $companies = Company::find()
                            ->innerJoinWith('divisions')
                            ->andWhere(['{{%divisions}}.category_id' => ServiceCategory::ROOT_STOMATOLOGY])
                            ->each();

        foreach ($companies as $company) {
            MedCardToothDiagnosis::generateDefault($company);
        }

        $diagnoses = MedCardToothHelper::getDiagnoses();

        /* @var MedCardTooth[] $teeth */
        $teeth = MedCardTooth::find()->all();
        foreach ($teeth as $tooth) {
            $company_id = $tooth->medCardTab
                ->medCard
                ->order
                ->companyCustomer
                ->company_id;

            /* @var MedCardToothDiagnosis $diagnosis */
            $diagnosis = MedCardToothDiagnosis::find()
                                              ->where([
                                                  'company_id' => $company_id,
                                                  'name'       => $diagnoses[$tooth->diagnosis_id]
                                              ])
                                              ->one();

            $tooth->teeth_diagnosis_id = $diagnosis->id;
            $tooth->update(false);
        }

        $this->dropColumn(
            '{{%med_card_tooth}}',
            'diagnosis_id'
        );
    }

    public function safeDown()
    {
        $this->addColumn(
            '{{%med_card_tooth}}',
            'diagnosis_id',
            $this->integer()->unsigned()
        );
        $diagnoses = MedCardToothHelper::getDiagnoses();

        /* @var MedCardTooth[] $teeth */
        $teeth = MedCardTooth::find()->all();
        foreach ($teeth as $tooth) {
            $diagnosis = $tooth->medCardTabTeethDiagnosis;
            $tooth->diagnosis_id = array_search($diagnosis->name, $diagnoses);
            $tooth->update(false);
        }

        $this->dropColumn('{{%med_card_tooth}}', 'teeth_diagnosis_id');
        $this->dropColumn('{{%med_card_tooth}}', 'id');

        MedCardToothDiagnosis::deleteAll([]);
    }
}
