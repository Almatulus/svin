<?php

use yii\db\Migration;

class m160629_115804_staff_payroll_unique extends Migration
{
    public function safeUp()
    {
        $this->createIndex('uq_staff_payroll_staff_started_date', 'crm_staff_payrolls', ['staff_id', 'started_time'], true);
    }

    public function safeDown()
    {
        $this->dropIndex('uq_staff_payroll_staff_started_date', 'crm_staff_payrolls');
    }
}
