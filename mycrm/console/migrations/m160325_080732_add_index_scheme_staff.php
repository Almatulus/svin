<?php

use yii\db\Migration;

class m160325_080732_add_index_scheme_staff extends Migration
{
    public function safeUp()
    {
        $this->dropTable('crm_payroll_scheme_staff');
        $this->addColumn('crm_staffs','scheme_id',$this->integer());
        $this->addForeignKey('fk_scheme','crm_staffs','scheme_id','crm_payroll_schemes','id');
    }

    public function safeDown()
    {
    }
}
