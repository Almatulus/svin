<?php

use yii\db\Migration;

class m170517_062844_add_order_tooth extends Migration
{
    public function safeUp()
    {
        $this->createTable("{{%order_tooth}}", [
            "order_id" => $this->integer()->notNull(),
            'teeth_num' => $this->integer()->notNull()
        ]);

        $this->addForeignKey('fk_teeth_order', '{{%order_tooth}}', 'order_id', '{{%orders}}', 'id');

        $this->createIndex('order_tooth_order_id', '{{%order_tooth}}', 'order_id');
        $this->createIndex('uq_order_tooth_order_teeth', '{{%order_tooth}}', ['order_id', 'teeth_num'], true);    }

    public function safeDown()
    {
        $this->dropTable("{{%order_tooth}}");
    }
}
