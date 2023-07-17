<?php

use yii\db\Migration;

class m160406_070027_alter_order_notification extends Migration
{
    public function safeUp()
    {
        $this->addColumn("crm_orders", "notify_status", $this->integer()->notNull()->defaultValue(0));
        $this->addColumn("crm_orders", "hours_before", $this->integer()->notNull()->defaultValue(0));
    }

    public function safeDown()
    {
        $this->dropColumn("crm_orders", "notify_status");
        $this->dropColumn("crm_orders", "hours_before");
    }
}
