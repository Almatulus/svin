<?php

use core\models\finance\CompanyContractor;
use yii\db\Migration;

class m170525_075616_add_division_id_to_contractor extends Migration
{
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
        $this->addColumn('{{%company_contractors}}', 'division_id', $this->integer());

        $allHasDivision = $this->setDivision();

        if ($allHasDivision) {
            $this->execute('ALTER TABLE {{%company_contractors}} ALTER COLUMN division_id SET NOT NULL');
        }

        $this->addForeignKey('fk_cashflow_division', '{{%company_contractors}}', 'division_id', '{{%divisions}}', 'id');
        $this->createIndex('company_contractor_division_id_idx', '{{%company_contractors}}', 'division_id');

        $this->dropColumn('{{%company_contractors}}', 'company_id');
    }

    public function safeDown()
    {
        $this->dropColumn('{{%company_contractors}}', 'division_id');
    }

    private function setDivision()
    {
        $contractors = CompanyContractor::find()->all();

        $allHasDivision = true;
        foreach ($contractors as $key => $contractor) {
            $division_id = null;
            if (!$division_id) {
                $division_id = $contractor->company->getDivisions()->select('id')->orderBy('id ASC')->scalar();
            }

            echo "{$contractor->id} : {$contractor->company_id} : {$division_id}\n";

            if ($division_id) {
                $contractor->updateAttributes(['division_id' => $division_id]);
            } else {
                $allHasDivision = false;
            }
        }

        return $allHasDivision;
    }
}
