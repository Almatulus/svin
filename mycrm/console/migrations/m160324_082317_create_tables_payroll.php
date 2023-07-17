<?php

use yii\db\Migration;

class m160324_082317_create_tables_payroll extends Migration
{
    public function safeUp()
    {
        $this->createTable('crm_payroll_schemes', [
            'id' => $this->primaryKey(),
            'name' => $this->string(255)->notNull(),
            'service_value' => $this->integer()->notNull()->defaultValue(0),
            'service_mode' => $this->smallInteger()->notNull()->defaultValue(0),
            'salary' => $this->integer()->notNull(),
            'salary_mode' => $this->smallInteger()->notNull()->defaultValue(0),
            'is_count_discount' => $this->boolean()->notNull()->defaultValue(false),
            'company_id' => $this->integer()->notNull(),
        ]);
        $this->addForeignKey('fk_company', 'crm_payroll_schemes', 'company_id', 'crm_companies', 'id');

        $this->createTable('crm_payroll_services', [
            'id' => $this->primaryKey(),
            'service_id' => $this->integer()->notNull(),
            'service_value' => $this->integer()->notNull()->defaultValue(0),
            'service_mode' => $this->smallInteger()->notNull()->defaultValue(0),
            'scheme_id' => $this->integer()->notNull(),
        ]);
        $this->addForeignKey('fk_service', 'crm_payroll_services', 'service_id', 'crm_services', 'id');
        $this->addForeignKey('fk_scheme', 'crm_payroll_services', 'scheme_id', 'crm_payroll_schemes', 'id');

        $this->createTable('crm_payroll_scheme_staff', [
            'id' => $this->primaryKey(),
            'staff_id' => $this->integer()->notNull(),
            'scheme_id' => $this->integer()->notNull(),
        ]);
        $this->addForeignKey('fk_staff', 'crm_payroll_scheme_staff', 'staff_id', 'crm_staffs', 'id');
        $this->addForeignKey('fk_scheme', 'crm_payroll_scheme_staff', 'scheme_id', 'crm_payroll_schemes', 'id');
        $this->createIndex("uq_staff_scheme", "crm_payroll_scheme_staff", ["staff_id", "scheme_id"], true);
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_staff', 'crm_payroll_scheme_staff');
        $this->dropForeignKey('fk_scheme', 'crm_payroll_scheme_staff');
        $this->dropTable('crm_payroll_scheme_staff');

        $this->dropForeignKey('fk_scheme', 'crm_payroll_services');
        $this->dropTable('crm_payroll_services');

        $this->dropForeignKey('fk_company', 'crm_payroll_schemes');
        $this->dropTable('crm_payroll_schemes');
    }
}
