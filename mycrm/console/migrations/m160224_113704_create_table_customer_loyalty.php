<?php

use yii\db\Migration;
use yii\db\pgsql\Schema;

class m160224_113704_create_table_customer_loyalty extends Migration
{
    public function safeUp()
    {
        $this->createTable('crm_customer_loyalties', [
            'id' => Schema::TYPE_PK,
            'type' => Schema::TYPE_SMALLINT,
            'amount' => Schema::TYPE_INTEGER.' NOT NULL',
            'percent' => Schema::TYPE_SMALLINT,
            'rank' => Schema::TYPE_SMALLINT,
            'category_id' => Schema::TYPE_INTEGER,
            'expire_days' => Schema::TYPE_INTEGER,
        ]);
        $this->addForeignKey('fk_category', 'crm_customer_loyalties', 'category_id', 'crm_customer_categories', 'id');
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_category', 'crm_customer_loyalties');
        $this->dropTable('crm_customer_loyalties');
    }
}
