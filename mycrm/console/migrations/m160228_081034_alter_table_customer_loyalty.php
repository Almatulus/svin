<?php

use yii\db\Migration;
use yii\db\Schema;

class m160228_081034_alter_table_customer_loyalty extends Migration
{
    public function safeUp()
    {
        $this->dropColumn('crm_customer_loyalties','expire_days');
        $this->renameColumn('crm_customer_loyalties','type','event');
        $this->renameColumn('crm_customer_loyalties','percent','discount');
    }

    public function safeDown()
    {
        $this->addColumn('crm_customer_loyalties','expire_days',Schema::TYPE_INTEGER);
        $this->renameColumn('crm_customer_loyalties','event','type');
        $this->renameColumn('crm_customer_loyalties','discount','percent');
    }

}
