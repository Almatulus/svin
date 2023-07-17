<?php

use yii\db\Migration;

class m160404_053645_alter_services extends Migration
{
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
        $this->addColumn("crm_division_services", "service_name", $this->string()->notNull()->defaultValue(""));
    }

    public function safeDown()
    {
        $this->dropColumn("crm_division_services", "service_name");
    }
}
