<?php

use yii\db\Migration;

class m170218_081740_add_payment_to_order extends Migration
{
    /*public function up()
    {

    }

    public function down()
    {
        echo "m170218_081740_add_payment_to_order cannot be reverted.\n";

        return false;
    }*/

    
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
        $this->addColumn('{{%orders}}', 'payment_id', $this->integer()->unsigned());
        $this->addForeignKey('fk_order_payment', '{{%orders}}', 'payment_id', '{{%payments}}', 'id');
    }

    public function safeDown()
    {
        $this->dropColumn('{{%orders}}', 'payment_id');
    }
    
}
