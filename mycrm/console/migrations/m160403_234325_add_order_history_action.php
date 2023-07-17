<?php

use yii\db\Migration;

class m160403_234325_add_order_history_action extends Migration
{
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
        $this->addColumn("crm_order_history", "action", $this->integer()->notNull()->defaultValue(0));
    }

    public function safeDown()
    {
        $this->dropColumn("crm_order_history", "action");
    }
}
