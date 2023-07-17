<?php

use yii\db\Schema;
use yii\db\Migration;

class m160210_051246_create_customer_category extends Migration
{
    public function safeUp()
    {
        $this->createTable('crm_customer_category', [
            'id' => Schema::TYPE_PK,
            'name' => "varchar(127) UNIQUE",
            'color' => "varchar(7) DEFAULT '#888888'"
        ]);
        $this->createTable('crm_customer_category_map', [
            'id' => Schema::TYPE_PK,
            'customer_id' => Schema::TYPE_INTEGER,
            'category_id' => Schema::TYPE_INTEGER
        ]);
        $this->addForeignKey('fk_customer', 'crm_customer_category_map', 'customer_id', 'crm_customers', 'id');
        $this->addForeignKey('fk_category', 'crm_customer_category_map', 'category_id', 'crm_customer_category', 'id');
    }

    public function safeDown()
    {
        $this->dropTable('crm_customer_category_map');
        $this->dropTable('crm_customer_category');
    }

}
