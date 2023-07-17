<?php

use yii\db\Schema;
use yii\db\Migration;

class m160210_080846_supplement_customers extends Migration
{
    public function safeUp()
    {
        $this->addColumn('crm_customers','name',Schema::TYPE_STRING." NOT NULL DEFAULT ''");
        $this->addColumn('crm_customers','discount',Schema::TYPE_SMALLINT." NOT NULL DEFAULT 0");
        $this->addColumn('crm_customers','email',Schema::TYPE_STRING);
    }

    public function safeDown()
    {
        $this->dropColumn('crm_customers','name');
        $this->dropColumn('crm_customers','discount');
        $this->dropColumn('crm_customers','email');
    }
}
