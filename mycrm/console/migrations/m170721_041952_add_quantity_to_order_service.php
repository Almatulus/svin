<?php

use yii\db\Migration;

class m170721_041952_add_quantity_to_order_service extends Migration
{
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
        $this->addColumn('{{%order_services}}', 'quantity', $this->integer()->unsigned());
        $this->update('{{%order_services}}', ['quantity' => 1]);
        $this->execute('ALTER TABLE {{%order_services}} ALTER COLUMN quantity SET NOT NULL');
    }

    public function safeDown()
    {
        $this->dropColumn('{{%order_services}}', 'quantity');
    }
}
