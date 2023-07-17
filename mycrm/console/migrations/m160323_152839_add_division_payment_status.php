<?php

use yii\db\Migration;

class m160323_152839_add_division_payment_status extends Migration
{
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
        $this->addColumn("crm_division_payments", "status", $this->integer()->notNull()->defaultValue(1));
    }

    public function safeDown()
    {
        $this->dropColumn("crm_division_payments", "status");
    }
}
