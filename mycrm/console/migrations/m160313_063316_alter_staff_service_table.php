<?php

use yii\db\Migration;

class m160313_063316_alter_staff_service_table extends Migration
{
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
        $this->dropColumn("crm_orders", "staff_service_id");
        $this->dropTable("crm_staff_services");

        $this->createTable('crm_division_services', [
            'id' => $this->primaryKey(),
            'service_id' => $this->integer()->notNull(),
            'division_id' => $this->integer()->notNull(),
            'price' => $this->integer()->notNull(),
            'average_time' => $this->integer()->notNull(),
        ]);
        $this->addForeignKey("fk_division_services_service", "crm_division_services", "service_id", "crm_services", "id");
        $this->addForeignKey("fk_division_services_division", "crm_division_services", "division_id", "crm_divisions", "id");
    }

    public function safeDown()
    {
        return false;
    }
}
