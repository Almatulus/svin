<?php

use core\models\finance\CompanyCash;
use yii\db\Migration;

class m170525_033808_add_division_id_to_company_cash extends Migration
{
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
        $this->addColumn('{{%company_cashes}}', 'division_id', $this->integer());

        $allHasDivision = $this->setDivision();

        if ($allHasDivision) {
            $this->execute('ALTER TABLE {{%company_cashes}} ALTER COLUMN division_id SET NOT NULL');
        }

        $this->addForeignKey('fk_cash_division', '{{%company_cashes}}', 'division_id', '{{%divisions}}', 'id');
        $this->createIndex('company_cash_division_id_idx', '{{%company_cashes}}', 'division_id');
    }

    public function safeDown()
    {
        $this->dropColumn('{{%company_cashes}}', 'division_id');
    }

    private function setDivision()
    {
        $cashes = CompanyCash::find()->all();

        $allHasDivision = true;
        foreach ($cashes as $key => $cash) {
            $division_id = $cash->company->getDivisions()->select('id')->orderBy('id ASC')->scalar();

            echo "{$cash->id} : {$cash->company_id} : {$division_id}\n";

            if ($division_id) {
                $cash->updateAttributes(['division_id' => $division_id]);
            } else {
                $allHasDivision = false;
            }
        }

        return $allHasDivision;
    }
}
