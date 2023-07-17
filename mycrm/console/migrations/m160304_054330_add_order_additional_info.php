<?php

use yii\db\Migration;

class m160304_054330_add_order_additional_info extends Migration
{
    public function safeUp()
    {
        $this->addColumn("crm_orders", "price", $this->integer()->notNull()->defaultValue(0));
        $this->addColumn("crm_orders", "note", $this->text());
        $this->addColumn("crm_order_history", "price", $this->integer()->notNull()->defaultValue(0));
        $this->addColumn("crm_order_history", "note", $this->text());
    }

    public function safeDown()
    {
        $this->dropColumn("crm_orders", "price");
        $this->dropColumn("crm_orders", "note");
        $this->dropColumn("crm_order_history", "price");
        $this->dropColumn("crm_order_history", "note");
    }
}
