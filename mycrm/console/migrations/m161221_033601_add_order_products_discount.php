<?php

use yii\db\Migration;

class m161221_033601_add_order_products_discount extends Migration
{
    // public function up()
    // {

    // }

    // public function down()
    // {
    //     echo "m161221_033601_add_order_products_discount cannot be reverted.\n";

    //     return false;
    // }

    
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
        $this->addColumn('{{%orders}}', 'products_discount', $this->integer()->defaultValue(0));
    }

    public function safeDown()
    {
        $this->dropColumn('{{%orders', 'products_discount');
    }
    
}
