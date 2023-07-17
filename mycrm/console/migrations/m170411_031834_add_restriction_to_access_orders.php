<?php

use yii\db\Migration;

class m170411_031834_add_restriction_to_access_orders extends Migration
{
    // public function up()
    // {

    // }

    // public function down()
    // {
    //     echo "m170411_031834_add_restriction_to_access_orders cannot be reverted.\n";

    //     return false;
    // }


    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
        $this->addColumn("{{%staffs}}", "see_own_orders", $this->boolean()->defaultValue(false));
    }

    public function safeDown()
    {
        $this->dropColumn("{{%staffs}}", "see_own_orders");
    }

}
