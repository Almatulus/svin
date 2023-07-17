<?php

use yii\db\Migration;

class m160529_201253_customer_request_message_length extends Migration
{
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
        $this->alterColumn("crm_customer_requests", "code", $this->text());
    }

    public function safeDown()
    {
        $this->alterColumn("crm_customer_requests", "code", $this->string());
    }
}
