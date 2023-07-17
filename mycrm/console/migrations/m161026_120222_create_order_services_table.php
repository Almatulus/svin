<?php

use core\models\order\Order;
use yii\db\Migration;

/**
 * Handles the creation for table `order_services`.
 */
class m161026_120222_create_order_services_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTable('{{%order_services}}', [
            'id' => $this->primaryKey(),
            'order_id' => $this->integer()->notNull(),
            'division_service_id' => $this->integer()->notNull()
        ]);

        $this->addForeignKey('fk_order', '{{%order_services}}', 'order_id', '{{%orders}}', 'id');
        $this->addForeignKey('fk_service', '{{%order_services}}', 'division_service_id', '{{%division_services}}', 'id');

        $this->alterColumn('{{%orders}}', 'division_service_id', "DROP NOT NULL");

        $orders = Order::find()->all();
        foreach ($orders as $key => $order) {
            /* @var Order $order */
            $this->insert("{{%order_services}}", [
                'order_id' => $order->id,
                'division_service_id' => $order->division_service_id
            ]);
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->alterColumn('{{%orders}}', 'division_service_id', "SET NOT NULL");

        $this->dropForeignKey('fk_order', '{{%order_services}}');
        $this->dropForeignKey('fk_service', '{{%order_services}}');
        $this->dropTable('{{%order_services}}');
    }
}
