<?php

use yii\db\Migration;

class m170626_094356_add_payment_to_sale extends Migration
{
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
        $this->addColumn('{{%warehouse_sale}}', 'payment_id', $this->integer()->unsigned());
        $this->addForeignKey('fk_sale_payment', '{{%warehouse_sale}}', 'payment_id', '{{%payments}}', 'id');
    }

    public function safeDown()
    {
        $this->dropColumn('{{%warehouse_sale}}', 'payment_id');
    }
}
