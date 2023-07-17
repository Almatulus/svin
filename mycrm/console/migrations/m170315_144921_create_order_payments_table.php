<?php

use core\models\order\Order;
use core\models\order\OrderPayment;
use yii\db\Migration;

/**
 * Handles the creation of table `order_payments`.
 */
class m170315_144921_create_order_payments_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTable('{{%order_payments}}', [
            'id' => $this->primaryKey(),
            'order_id' => $this->integer()->notNull(),
            'payment_id' => $this->integer()->notNull(),
            'amount' => $this->integer()->notNull()->defaultValue(0),
        ]);
        $this->addForeignKey('fk_order_payment_order', '{{%order_payments}}', 'order_id', '{{%orders}}', 'id');
        $this->addForeignKey('fk_order_payment_payment', '{{%order_payments}}', 'payment_id', '{{%payments}}', 'id');

        echo "-- Start moving payments --\n";
        $this->movePayments();
        echo "-- Finish moving payments --\n";
        $this->dropColumn('{{%orders}}', 'payment_id');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->addColumn('{{%orders}}', 'payment_id', $this->integer());
        $this->addForeignKey('fk_order_payment', '{{%orders}}', 'payment_id', '{{%payments}}', 'id');

        echo "-- Start reverting payments --\n";
        $this->revertPayments();
        echo "-- Finish reverting payments --\n";


        $this->dropTable('{{%order_payments}}');
    }

    private function movePayments()
    {
        /* @var Order[] $orders */
        $orders = Order::find()->orderBy('id')->all();
        foreach ($orders as $order) {
            if (empty($order->payment_id)) {
                echo "- {$order->id} is skipped\n";
                continue;
            }
            $orderPayment = new OrderPayment();
            $orderPayment->order_id = $order->id;
            $orderPayment->amount = $order->price;
            $orderPayment->payment_id = $order->payment_id;
            if (!$orderPayment->save()) {
                throw new Exception('Order payment save exception');
            }
            echo "- {$order->id} is moved\n";
        }

    }

    private function revertPayments()
    {
        /* @var OrderPayment[] $ordersPayments */
        $ordersPayments = OrderPayment::find()->joinWith(['order'])->orderBy('order_id')->all();

        foreach ($ordersPayments as $payment) {
            $payment->order->updateAttributes(['payment_id' => $payment->payment_id]);
            echo "- {$payment->order_id} is reverted\n";
        }
    }
}
