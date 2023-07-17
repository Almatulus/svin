<?php

use yii\db\Schema;
use yii\db\Migration;

class m160210_181810_alter_table_customer_category_map extends Migration
{
    public function safeUp()
    {
        $this->alterColumn("crm_customer_category_map", "customer_id", 'SET NOT NULL');
        $this->alterColumn("crm_customer_category_map", "category_id", 'SET NOT NULL');
    }

    public function safeDown()
    {
        $this->alterColumn("crm_customer_category_map", "customer_id", Schema::TYPE_INTEGER);
        $this->alterColumn("crm_customer_category_map", "category_id", Schema::TYPE_INTEGER);
    }
}
