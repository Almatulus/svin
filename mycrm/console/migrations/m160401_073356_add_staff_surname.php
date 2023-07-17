<?php

use yii\db\Migration;

class m160401_073356_add_staff_surname extends Migration
{
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
        $this->addColumn("crm_staffs", "surname", $this->string()->notNull()->defaultValue(""));
        $this->addColumn("crm_staffs", "gender", $this->integer()->notNull()->defaultValue(1));
        $this->addColumn("crm_staffs", "iin", $this->string(12)->defaultValue(1));
        $this->addColumn("crm_staffs", "description_private", $this->text());
        $this->addColumn("crm_staffs", "phone", $this->string());
        $this->addColumn("crm_staffs", "has_calendar", $this->integer()->notNull()->defaultValue(1));
        $this->addColumn("crm_staffs", "color", $this->string()->notNull()->defaultValue(""));
    }

    public function safeDown()
    {
        $this->dropColumn("crm_staffs", "surname");
        $this->dropColumn("crm_staffs", "gender");
        $this->dropColumn("crm_staffs", "iin");
        $this->dropColumn("crm_staffs", "description_private");
        $this->dropColumn("crm_staffs", "phone");
        $this->dropColumn("crm_staffs", "has_calendar");
        $this->dropColumn("crm_staffs", "color");
    }
}
