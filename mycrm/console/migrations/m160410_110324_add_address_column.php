<?php

use yii\db\Migration;

class m160410_110324_add_address_column extends Migration
{
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
        $this->addColumn("crm_company_customers", "address", $this->text());
    }

    public function safeDown()
    {
        $this->dropColumn("crm_company_customers", "address");
    }
}
