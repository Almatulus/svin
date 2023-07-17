<?php

use yii\db\Migration;

class m160624_083214_set_staff_user extends Migration
{
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
        $this->addColumn("crm_staffs", "user_id", $this->integer());
        $this->addColumn("crm_staffs", "user_email", $this->string());
        $this->addForeignKey('fk_staffs_user', 'crm_staffs', 'user_id', 'crm_users', 'id');
    }

    public function safeDown()
    {
        $this->dropColumn("crm_staffs", "user_id");
    }
}
