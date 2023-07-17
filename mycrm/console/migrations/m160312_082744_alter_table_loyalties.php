<?php

use yii\db\Migration;

class m160312_082744_alter_table_loyalties extends Migration
{
    public function safeUp()
    {
        $this->update('crm_customer_loyalties',['event' => 0],['event' => null]);
        $this->alterColumn('crm_customer_loyalties','event','SET DEFAULT 0');
        $this->alterColumn('crm_customer_loyalties','event','SET NOT NULL');
    }

    public function safeDown()
    {
    }
}
