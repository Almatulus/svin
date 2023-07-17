<?php

use yii\db\Migration;

class m160313_072846_alter_order_table extends Migration
{
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
        $this->update("crm_staff_schedules", ["order_id" => null]);
        $this->delete("crm_orders");
        $this->addColumn("crm_orders", "division_service_id", $this->integer()->notNull());
        $this->addColumn("crm_orders", "staff_id", $this->integer());
        $this->addForeignKey("fk_order_division_service",
            "crm_orders", "division_service_id", "crm_division_services", "id");
        $this->addForeignKey("fk_order_staff",
            "crm_orders", "staff_id", "crm_staffs", "id");
    }

    public function safeDown()
    {
        $this->dropColumn("crm_orders", "division_service_id");
    }
}
