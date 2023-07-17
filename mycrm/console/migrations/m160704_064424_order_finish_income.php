<?php

use yii\db\Migration;

class m160704_064424_order_finish_income extends Migration
{
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
        $this->addColumn("crm_company_cost_items", "is_order", $this->integer()->defaultValue(0));
    }

    public function safeDown()
    {
        $this->dropColumn("crm_company_cost_items", "is_order");
    }
}
