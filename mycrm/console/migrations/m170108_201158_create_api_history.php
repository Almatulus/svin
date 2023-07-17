<?php

use yii\db\Migration;

class m170108_201158_create_api_history extends Migration
{
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
        $this->createTable('{{%api_history}}', [
            'id' => $this->primaryKey(),
            'ip' => $this->string(45),
            'created_time' => $this->dateTime()->notNull(),
            'url' => $this->string(255),
            'customer_id' => $this->integer(),
            'request_header' => $this->text(),
            'response_header' => $this->text(),
            'response_body' => $this->text(),
            'response_status_code' => $this->integer(),
        ]);
        $this->createIndex("ix", "{{%api_history}}", "ip");
        $this->createIndex("created_time", "{{%api_history}}", "ip");
    }

    public function safeDown()
    {
        $this->dropTable('{{%api_history}}');
    }
}
