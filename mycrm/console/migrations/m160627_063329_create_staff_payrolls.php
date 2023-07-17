<?php

use yii\db\Migration;

/**
 * Handles the creation for table `staff_payrolls`.
 */
class m160627_063329_create_staff_payrolls extends Migration
{
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
        $this->createTable('crm_staff_payrolls', [
            'id' => $this->primaryKey(),
            'staff_id' => $this->integer()->notNull(),
            'payroll_id' => $this->integer()->notNull(),
            'started_time' => $this->date(),
            'created_time' => $this->dateTime(),
        ]);
        $this->addForeignKey('fk_staff_payrolls_staff', 'crm_staff_payrolls', 'staff_id', 'crm_staffs', 'id');
        $this->addForeignKey('fk_staff_payrolls_payroll', 'crm_staff_payrolls', 'payroll_id', 'crm_payrolls', 'id');
        $this->dropColumn("crm_staffs", "scheme_id");
    }

    public function safeDown()
    {
        $this->dropTable("crm_staff_payrolls");
    }
}
