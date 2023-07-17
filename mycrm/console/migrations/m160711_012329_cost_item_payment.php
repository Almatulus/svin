<?php

use yii\db\Migration;

class m160711_012329_cost_item_payment extends Migration
{
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
        $this->addColumn("crm_company_cost_items", "is_salary", $this->integer()->defaultValue(0));
    }

    public function safeDown()
    {
        $this->dropColumn("crm_company_cost_items", "is_salary");
    }
}
