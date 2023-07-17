<?php

use yii\db\Migration;

class m160302_184802_add_order_datetime extends Migration
{
    public function up()
    {
        $this->addColumn("crm_orders", "datetime", $this->dateTime()->notNull()->defaultValue("now()"));
    }

    public function down()
    {
        $this->dropColumn("crm_orders", "datetime");
    }

    /*
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
    }

    public function safeDown()
    {
    }
    */
}
