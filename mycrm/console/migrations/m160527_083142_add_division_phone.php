<?php

use yii\db\Migration;

class m160527_083142_add_division_phone extends Migration
{
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
        $this->addColumn("crm_divisions", "phone", $this->string()->notNull()->defaultValue(""));
    }

    public function safeDown()
    {
        $this->dropColumn("crm_divisions", "phone");
    }
}
