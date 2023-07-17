<?php

use yii\db\Migration;

class m161209_061949_add_order_product extends Migration
{
    public function up()
    {
        $this->createTable('{{%order_service_products}}', [
            'id' => $this->primaryKey(),
            'order_id' => $this->integer()->notNull(),
            'order_service_id' => $this->integer()->notNull(),
            'product_id' => $this->integer()->notNull(),
            'quantity' => $this->double()->notNull(),
        ]);

        $this->addForeignKey('fk_product_order', '{{%order_service_products}}', 'order_id', '{{%orders}}', 'id');
        $this->addForeignKey('fk_product_order_service', '{{%order_service_products}}', 'order_service_id', '{{%order_services}}', 'id');
        $this->addForeignKey('fk_product', '{{%order_service_products}}', 'product_id', '{{%warehouse_product}}', 'id');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('{{%order_service_products}}');
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
