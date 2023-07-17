<?php

use yii\db\Schema;
use yii\db\Migration;

class m160215_051159_rename_table_customer_category extends Migration
{
    public function safeUp()
    {
        $this->renameTable('crm_customer_category', 'crm_customer_categories');
        $this->addColumn('crm_customers','comments',Schema::TYPE_TEXT);

    }

    public function safeDown()
    {
        $this->renameTable('crm_customer_categories', 'crm_customer_category');
        $this->dropColumn('crm_customers','comments');
    }
}
