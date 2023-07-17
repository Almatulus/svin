<?php

use yii\db\Migration;

class m160912_221613_add_division_description extends Migration
{
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
        $this->addColumn("crm_divisions", "description", $this->text());
    }

    public function safeDown()
    {
        $this->dropColumn("crm_divisions", "description");
    }
}
