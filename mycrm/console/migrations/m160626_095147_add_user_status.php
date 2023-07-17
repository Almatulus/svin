<?php

use yii\db\Migration;

class m160626_095147_add_user_status extends Migration
{
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
        $this->addColumn("crm_users", "status", $this->integer()->notNull()->defaultValue(1));
    }

    public function safeDown()
    {
        $this->dropColumn("crm_users", "status");
    }
}
