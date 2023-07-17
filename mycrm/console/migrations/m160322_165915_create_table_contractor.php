<?php

use yii\db\Migration;

class m160322_165915_create_table_contractor extends Migration
{
    public function safeUp()
    {
        $this->addColumn('crm_customers','created_time',\yii\db\oci\Schema::TYPE_DATETIME.' NOT NULL DEFAULT now()');
        $this->addColumn('crm_company_customers','created_time',\yii\db\oci\Schema::TYPE_DATETIME.' NOT NULL DEFAULT now()');
        $this->addColumn('crm_company_customers','status',$this->smallInteger()->notNull()->defaultValue(0));

        $this->createTable('crm_company_contractors',[
            'id' => $this->primaryKey(),
            'type' => $this->smallInteger()->notNull()->defaultValue(0),
            'name' => $this->string(255)->notNull(),
            'company_id' => $this->integer()->notNull(),
            'iin' => $this->string(31),
            'kpp' => $this->string(31),
            'contacts' => $this->string(255),
            'phone' => $this->string(255),
            'email' => $this->string(255),
            'address' => $this->string(255),
            'comments' => $this->text(),
        ]);

        $this->addForeignKey('fk_company', 'crm_company_contractors', 'company_id', 'crm_companies', 'id');
    }

    public function safeDown()
    {
        $this->dropColumn("crm_customers", "created_time");
        $this->dropColumn("crm_company_customers", "created_time");
        $this->dropColumn("crm_company_customers", "status");

        $this->dropForeignKey('fk_company', 'crm_company_contractors');
        $this->dropTable('crm_company_contractors');
    }
}
