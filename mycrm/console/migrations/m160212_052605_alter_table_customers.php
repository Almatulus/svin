<?php

use yii\db\Schema;
use yii\db\Migration;

class m160212_052605_alter_table_customers extends Migration
{
    public function safeUp()
    {
        $this->addColumn('crm_customers','gender', Schema::TYPE_BOOLEAN);
        $this->addColumn('crm_customers','birth_date', Schema::TYPE_DATE);
        $this->addColumn('crm_customers','rank', Schema::TYPE_SMALLINT.' NOT NULL DEFAULT 0');
    }

    public function safeDown()
    {
        $this->dropColumn('crm_customers', 'gender');
        $this->dropColumn('crm_customers', 'birth_date');
        $this->dropColumn('crm_customers','rank');
    }
}
