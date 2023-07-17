<?php

use yii\db\Migration;

class m160708_032244_add_staff_permissions extends Migration
{
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
        $this->addColumn("crm_staffs", "user_permissions", $this->text());
    }

    public function safeDown()
    {
        $this->dropColumn("crm_staffs", "user_permissions");
    }
}
