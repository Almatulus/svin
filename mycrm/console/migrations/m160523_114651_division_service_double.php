<?php

use yii\db\Migration;

class m160523_114651_division_service_double extends Migration
{
    public function safeUp()
    {
        $this->dropColumn("crm_division_services", "service_id");
        $this->createTable("crm_division_services_map", [
            'id' => $this->primaryKey(),
            'service_id' => $this->integer()->notNull(),
            'division_service_id' => $this->integer()->notNull(),
        ]);

        $this->addForeignKey('fk_division_services_map_service', 'crm_division_services_map', 'service_id', 'crm_services', 'id');
        $this->addForeignKey('fk_division_services_map_division_service', 'crm_division_services_map', 'division_service_id', 'crm_division_services', 'id');
        $this->createIndex("uq_division_services_map_service_division_service", "crm_division_services_map", ["service_id", "division_service_id"], true);
    }

    public function safeDown()
    {
        return false;
    }
}
