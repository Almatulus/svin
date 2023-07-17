<?php

use yii\db\Migration;

class m160323_085849_create_table_cashbox extends Migration
{
    public function safeUp()
    {
        $this->createTable('crm_company_cashes', [
            'id' => $this->primaryKey(),
            'name' => $this->string(255)->notNull(),
            'company_id' => $this->integer()->notNull(),
            'type' => $this->smallInteger()->notNull()->defaultValue(0),
            'init_money' => $this->integer()->notNull()->defaultValue(0),
            'comments' => $this->text(),
        ]);
        $this->addForeignKey('fk_company', 'crm_company_cashes', 'company_id', 'crm_companies', 'id');

        $this->createTable('crm_company_cost_items', [
            'id' => $this->primaryKey(),
            'name' => $this->string(255)->notNull(),
            'type' => $this->smallInteger()->notNull()->defaultValue(0),
            'comments' => $this->text(),
            'company_id' => $this->integer()->notNull(),
        ]);
        $this->addForeignKey('fk_company', 'crm_company_cost_items', 'company_id', 'crm_companies', 'id');
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_company', 'crm_company_cashes');
        $this->dropTable('crm_company_cashes');

        $this->dropForeignKey('fk_company', 'crm_company_cost_items');
        $this->dropTable('crm_company_cost_items');
    }
}
