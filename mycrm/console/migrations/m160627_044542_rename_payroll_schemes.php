<?php

use yii\db\Migration;

class m160627_044542_rename_payroll_schemes extends Migration
{
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
        $this->renameTable("crm_payroll_schemes", "crm_payrolls");
    }

    public function safeDown()
    {
        $this->renameTable("crm_payrolls", "crm_payroll_schemes");
    }
}
