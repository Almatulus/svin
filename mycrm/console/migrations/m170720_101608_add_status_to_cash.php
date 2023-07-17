<?php

use yii\db\Migration;

class m170720_101608_add_status_to_cash extends Migration
{
   public function safeUp()
    {
        $this->addColumn("{{%company_cashes}}", "status", $this->integer()->unsigned()->defaultValue(1));
    }

    public function safeDown()
    {
        $this->dropColumn("{{%company_cashes}}", "status");
    }
}
