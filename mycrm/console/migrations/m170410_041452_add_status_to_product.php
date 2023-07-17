<?php

use yii\db\Migration;

class m170410_041452_add_status_to_product extends Migration
{
    /*public function up()
    {

    }

    public function down()
    {
        echo "m170410_041452_add_status_to_product cannot be reverted.\n";

        return false;
    }*/


    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
        $this->addColumn("{{%warehouse_product}}", "status", $this->integer()->defaultValue(1));
    }

    public function safeDown()
    {
        $this->dropColumn("{{%warehouse_product}}", "status");
    }

}
