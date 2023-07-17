<?php

use yii\db\Migration;

class m170318_034130_add_color_to_order extends Migration
{
    /*public function up()
    {

    }

    public function down()
    {
        echo "m170318_034130_add_color_to_order cannot be reverted.\n";

        return false;
    }*/


    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
        $this->addColumn('{{%orders}}', 'color', $this->string());
    }

    public function safeDown()
    {
        $this->dropColumn('{{%orders}}', 'color');
    }

}
