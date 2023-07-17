<?php

use yii\db\Migration;

class m160529_192721_customer_request_receiver extends Migration
{
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
        $this->addColumn("crm_customer_requests", "receiver_phone", $this->string()->notNull()->defaultValue(""));
    }

    public function safeDown()
    {
        $this->dropColumn("crm_customer_requests", "receiver_phone");
    }
}
