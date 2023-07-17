<?php

use yii\db\Migration;

class m170120_040030_add_discount_to_usage extends Migration
{
    /*public function up()
    {

    }

    public function down()
    {
        echo "m170120_040030_add_discount_to_usage cannot be reverted.\n";

        return false;
    }*/

    
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
        $this->addColumn('{{%warehouse_usage}}', 'discount', $this->integer()->unsigned()->defaultValue(0));
    }

    public function safeDown()
    {
        $this->dropColumn('{{%warehouse_usage}}', 'discount');
    }
    
}
