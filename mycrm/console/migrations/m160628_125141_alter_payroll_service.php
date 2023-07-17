<?php

use yii\db\Migration;

class m160628_125141_alter_payroll_service extends Migration
{
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
        $this->delete("crm_payroll_services");
        $this->dropColumn("crm_payroll_services", "service_id");
        $this->addColumn("crm_payroll_services", 'division_service_id', $this->integer()->notNull());
        $this->addForeignKey('fk_payroll_services_division_service', 'crm_payroll_services', 'division_service_id', 'crm_division_services', 'id');
    }

    public function safeDown()
    {
        return false;
    }
}
