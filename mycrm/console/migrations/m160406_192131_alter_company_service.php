<?php

use yii\db\Migration;

class m160406_192131_alter_company_service extends Migration
{
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
        $this->addColumn("crm_division_services", "description", $this->text());
    }

    public function safeDown()
    {
        $this->dropColumn("crm_division_services", "description");
    }
}
