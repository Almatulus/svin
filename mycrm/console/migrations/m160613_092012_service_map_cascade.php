<?php

use yii\db\Migration;

class m160613_092012_service_map_cascade extends Migration
{
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
        $this->dropForeignKey('fk_division_services_map_service', 'crm_division_services_map');
        $this->dropForeignKey('fk_division_services_map_division_service', 'crm_division_services_map');
        $this->addForeignKey('fk_division_services_map_service', 'crm_division_services_map', 'service_id', 'crm_services', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_division_services_map_division_service', 'crm_division_services_map', 'division_service_id', 'crm_division_services', 'id', 'CASCADE', 'CASCADE');
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_division_services_map_service', 'crm_division_services_map');
        $this->dropForeignKey('fk_division_services_map_division_service', 'crm_division_services_map');
        $this->addForeignKey('fk_division_services_map_service', 'crm_division_services_map', 'service_id', 'crm_services', 'id');
        $this->addForeignKey('fk_division_services_map_division_service', 'crm_division_services_map', 'division_service_id', 'crm_division_services', 'id');
    }
}
