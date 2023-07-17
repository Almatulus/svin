<?php

use core\models\finance\CompanyCashflow;
use yii\db\Migration;

class m170525_043456_add_division_id_to_cashflow extends Migration
{
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
        $this->addColumn('{{%company_cashflows}}', 'division_id', $this->integer());

        $allHasDivision = $this->setDivision();

        if ($allHasDivision) {
            $this->execute('ALTER TABLE {{%company_cashflows}} ALTER COLUMN division_id SET NOT NULL');
        }

        $this->addForeignKey('fk_cashflow_division', '{{%company_cashflows}}', 'division_id', '{{%divisions}}', 'id');
        $this->createIndex('company_cashflow_division_id_idx', '{{%company_cashflows}}', 'division_id');
    }

    public function safeDown()
    {
        $this->dropColumn('{{%company_cashflows}}', 'division_id');
    }

    private function setDivision()
    {
        $cashflows = CompanyCashflow::find();

        $allHasDivision = true;
        foreach ($cashflows->each(20) as $key => $cashflow) {
            $division_id = null;
            if (isset($cashflow->staff)) {
                $division_id = $cashflow->staff->division_id;
            }
            if (!$division_id) {
                $division_id = $cashflow->company->getDivisions()->select('id')->orderBy('id ASC')->scalar();
            }

            // echo "{$cashflow->id} : {$cashflow->company_id} : {$division_id}\n";

            if ($division_id) {
                $cashflow->updateAttributes(['division_id' => $division_id]);
            } else {
                $allHasDivision = false;
            }
        }

        return $allHasDivision;
    }
}
