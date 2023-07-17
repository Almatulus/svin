<?php

use yii\db\Migration;

class m160313_210526_set_customer_required extends Migration
{
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
        $this->delete("crm_orders");
        $this->dropColumn("crm_orders", "customer_id");
        $this->addColumn("crm_orders", "customer_id", $this->integer()->notNull());
        $this->addForeignKey("crm_orders_customer", "crm_orders", "customer_id", "crm_customers", "id");
    }

    public function safeDown()
    {
        $this->delete("crm_orders");
        $this->dropColumn("crm_orders", "customer_id");
        $this->addColumn("crm_orders", "customer_id", $this->integer());
        $this->addForeignKey("crm_orders_customer", "crm_orders", "customer_id", "crm_customers", "id");
    }
}
