<?php

use yii\db\Migration;

class m160628_130231_add_staff_hired_date extends Migration
{
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
        $this->addColumn("crm_staffs", "hired_date", $this->date()->notNull()->defaultValue("now()"));
    }

    public function safeDown()
    {
        $this->dropColumn("crm_staffs", "hired_date");
    }
}
