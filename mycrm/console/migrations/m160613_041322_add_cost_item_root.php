<?php

use yii\db\Migration;

class m160613_041322_add_cost_item_root extends Migration
{
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
        $this->addColumn("crm_company_cost_items", "is_root", $this->integer()->notNull()->defaultValue(0));
    }

    public function safeDown()
    {
        $this->dropColumn("crm_company_cost_items", "is_root");
    }
}
