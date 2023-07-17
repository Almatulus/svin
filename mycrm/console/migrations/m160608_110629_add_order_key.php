<?php

use yii\db\Migration;

class m160608_110629_add_order_key extends Migration
{
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
        $this->addColumn("crm_orders", "key", $this->string(45)->defaultValue(NULL));
        $this->createIndex("uq_orders_key", "crm_orders", "key", true);
    }

    public function safeDown()
    {
        $this->dropColumn("crm_orders", "key");
    }
}
