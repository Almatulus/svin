<?php

use yii\db\Migration;

class m160323_143220_add_division_unique_key extends Migration
{
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
        $this->addColumn("crm_divisions", "key", $this->string(45));
        $this->createIndex("uq_divisions_key", "crm_divisions", "key", true);
    }

    public function safeDown()
    {
        $this->dropColumn("crm_divisions", "key");
    }
}
