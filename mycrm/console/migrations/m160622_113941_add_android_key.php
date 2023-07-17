<?php

use yii\db\Migration;

class m160622_113941_add_android_key extends Migration
{
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
        $this->addColumn("crm_customers", "key_android", $this->string());
    }

    public function safeDown()
    {
        $this->dropColumn("crm_customers", "key_android");
    }
}
