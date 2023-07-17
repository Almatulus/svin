<?php

use yii\db\Migration;

class m160410_175646_alter_table_customers extends Migration
{
    public function safeUp()
    {
        $this->addColumn('crm_customers','lastname',$this->string(255));
        $this->addColumn('crm_company_customers','city',$this->string(255));
    }

    public function safeDown()
    {
        $this->dropColumn('crm_customers','lastname');
        $this->dropColumn('crm_company_customers','city');
    }
}
