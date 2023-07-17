<?php

use yii\db\Migration;

class m160313_162008_create_staff_division_services extends Migration
{
    public function safeUp()
    {
        $this->createTable('crm_staff_division_services', [
            'id' => $this->primaryKey(),
            'division_service_id' => $this->integer()->notNull(),
            'staff_id' => $this->integer()->notNull(),
        ]);

        $this->addForeignKey("fk_staff_division_services_division_service",
            "crm_staff_division_services", "division_service_id", "crm_division_services", "id");
        $this->addForeignKey("fk_staff_division_services_staff",
            "crm_staff_division_services", "staff_id", "crm_staffs", "id");
    }

    public function safeDown()
    {
        $this->dropTable('staff_division_services');
    }
}
