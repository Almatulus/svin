<?php

use yii\db\Migration;

class m161214_104846_add_product_purchase_price extends Migration
{
    public function up()
    {
        $this->addColumn('{{%warehouse_product}}', 'purchase_price', $this->double());
    }

    public function down()
    {
        $this->dropColumn('{{%warehouse_product}}', 'purchase_price');
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
