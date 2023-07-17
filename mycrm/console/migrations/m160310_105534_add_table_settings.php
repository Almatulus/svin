<?php

use yii\db\Migration;
use yii\db\Schema;

class m160310_105534_add_table_settings extends Migration
{
    public function safeUp()
    {
        $this->createTable('crm_customer_request_templates',[
            'id' => Schema::TYPE_PK,
            'key' => "varchar(127) NOT NULL",
            'is_enabled' => Schema::TYPE_BOOLEAN.' NOT NULL DEFAULT false',
            'template' => 'varchar(255) NOT NULL',
            'company_id' => Schema::TYPE_INTEGER.' NOT NULL',
            'UNIQUE (key,company_id)'
        ]);
        $this->addForeignKey('fk_settings_company', 'crm_customer_request_templates', 'company_id', 'crm_companies', 'id');
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_settings_company','crm_customer_request_templates');
        $this->dropTable('crm_customer_request_templates');
    }
}
