<?php

use yii\db\Migration;

class m160622_081737_customer_request_key extends Migration
{
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
        $this->createTable('crm_customer_request_keys',[
            'id' => $this->primaryKey(),
            'customer_id' => $this->integer()->notNull(),
            'key' => $this->string(255)->notNull(),
            'type' => $this->smallInteger()->notNull(),
            'status' => $this->integer()->notNull()->defaultValue(1),
            'created_time' => $this->dateTime(),
        ]);

        $this->addForeignKey('fk_customer_request_keys_customer', 'crm_customer_request_keys', 'customer_id', 'crm_customers', 'id');
    }

    public function safeDown()
    {
        $this->dropTable("crm_customer_request_keys");
    }
}
