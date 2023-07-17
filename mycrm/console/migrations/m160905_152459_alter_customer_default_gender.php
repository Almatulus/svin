<?php

use yii\db\Migration;

class m160905_152459_alter_customer_default_gender extends Migration
{
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
        $this->execute('ALTER TABLE "crm_customers" ALTER COLUMN "gender" SET DEFAULT 1;');
    }

    public function safeDown()
    {
    }
}
