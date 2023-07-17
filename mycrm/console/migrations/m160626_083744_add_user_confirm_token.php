<?php

use yii\db\Migration;

class m160626_083744_add_user_confirm_token extends Migration
{
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
        $this->addColumn("crm_staffs", "user_confirm", $this->string());
    }

    public function safeDown()
    {
        $this->dropColumn("crm_staffs", "user_confirm");
    }
}
