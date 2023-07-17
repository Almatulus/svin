<?php

use yii\db\Migration;

class m160708_043149_alter_staff_position extends Migration
{
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
        $this->execute("ALTER TABLE crm_staffs ALTER COLUMN company_position_id DROP NOT NULL;");
    }

    public function safeDown()
    {
    }
}
