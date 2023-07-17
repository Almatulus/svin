<?php

use yii\db\Migration;
use yii\db\Schema;

class m160313_164521_alter_tables_add_company_id extends Migration
{
    public function safeUp()
    {
        $this->addColumn('crm_customers','company_id',Schema::TYPE_INTEGER);
        $this->addForeignKey('fk_company','crm_customers','company_id','crm_companies','id');
        $this->update('crm_customers',['company_id' => 1]);
        $this->alterColumn('crm_customers','company_id','SET NOT NULL');

        $this->addColumn('crm_customer_loyalties','company_id',Schema::TYPE_INTEGER);
        $this->addForeignKey('fk_company','crm_customer_loyalties','company_id','crm_companies','id');
        $this->update('crm_customer_loyalties',['company_id' => 1]);
        $this->alterColumn('crm_customer_loyalties','company_id','SET NOT NULL');

        $this->addColumn('crm_customer_categories','company_id',Schema::TYPE_INTEGER);
        $this->addForeignKey('fk_company','crm_customer_categories','company_id','crm_companies','id');
        $this->update('crm_customer_categories',['company_id' => 1]);
        $this->alterColumn('crm_customer_categories','company_id','SET NOT NULL');
    }

    public function safeDown()
    {
        $this->dropColumn('crm_customers','company_id');
        $this->dropColumn('crm_customer_loyalties','company_id');
        $this->dropColumn('crm_customer_categories','company_id');
    }
}
