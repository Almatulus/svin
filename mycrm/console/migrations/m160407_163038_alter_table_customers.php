<?php

use yii\db\Migration;

class m160407_163038_alter_table_customers extends Migration
{
    public function safeUp()
    {
        $this->dropColumn('crm_customers', 'comments');
        $this->addColumn('crm_company_customers', 'comments', $this->text());
        $this->addColumn('crm_company_customers', 'attract', $this->integer()->defaultValue(0));
    }

    public function safeDown()
    {
        $this->addColumn('crm_customers', 'comments', $this->text());
        $this->dropColumn('crm_company_customers', 'comments');
        $this->dropColumn('crm_company_customers', 'attract');
    }
}
