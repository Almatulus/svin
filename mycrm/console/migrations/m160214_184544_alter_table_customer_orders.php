<?php

use yii\db\Schema;
use yii\db\Migration;

class m160214_184544_alter_table_customer_orders extends Migration
{
    public function safeUp()
    {
        $this->dropColumn('crm_orders', 'customer_name');
        $this->addColumn('crm_orders', 'discount', Schema::TYPE_SMALLINT.' NOT NULL DEFAULT 0');
    }

    public function safeDown()
    {
        $this->addColumn('crm_orders', 'customer_name', "varchar(255) NOT NULL DEFAULT ''");
        $this->dropColumn('crm_orders', 'discount');
    }
}
