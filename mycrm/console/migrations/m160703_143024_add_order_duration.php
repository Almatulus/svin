<?php

use yii\db\Migration;

class m160703_143024_add_order_duration extends Migration
{
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
        $this->addColumn("crm_orders", "duration", $this->integer()->notNull()->defaultValue(0));
    }

    public function safeDown()
    {
        $this->dropColumn("crm_orders", "duration");
    }
}
