<?php

use yii\db\Migration;

class m170510_180921_add_customer_balance extends Migration
{
    public function safeUp()
    {
        $this->addColumn("{{%company_customers}}", "balance", $this->integer()->notNull()->defaultValue(0));
        $this->insert("{{%payments}}", ["id" => 7, "name" => "debt", "status" => 1]);
    }

    public function safeDown()
    {
        $this->dropColumn("{{%company_customers}}", "balance");
        $this->update("{{%payments}}", ["status" => 0], ["id" => 7]);
    }
}
