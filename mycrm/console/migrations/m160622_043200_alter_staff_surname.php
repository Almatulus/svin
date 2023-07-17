<?php

use yii\db\Migration;

class m160622_043200_alter_staff_surname extends Migration
{
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
        $this->alterColumn("crm_staffs", "surname", $this->string());
    }

    public function safeDown()
    {
        $this->alterColumn("crm_staffs", "surname", $this->string());
    }
}
