<?php

use yii\db\Migration;

class m160226_032938_populate_customer_loyalty extends Migration
{
    public function safeUp()
    {
        $this->addColumn('crm_customer_loyalties','mode',\yii\db\mysql\Schema::TYPE_INTEGER.' NOT NULL DEFAULT 0');
        $this->insert('crm_customer_loyalties',['percent' => 0, 'expire_days' => 45, 'amount' => 0]);
        $this->insert('crm_customer_loyalties',['rank' => 0, 'expire_days' => 360, 'amount' => 0]);
    }

    public function safeDown()
    {
        $this->dropColumn('crm_customer_loyalties','mode');
        $this->delete('crm_customer_loyalties', ['percent' => 0, 'expire_days' => 45]);
        $this->delete('crm_customer_loyalties', ['rank' => 0, 'expire_days' => 360]);
    }
}
