<?php

use core\models\order\Order;
use yii\db\Migration;

class m170526_052843_fix_brow_up_orders extends Migration
{
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
        $incorrectOrders = [
            19168,
            19346,
            19364,
            19351,
            18766,
            19459,
            21280
        ];

        $orders = Order::findAll($incorrectOrders);
        foreach ($orders as $key => $order) {
            $servicesPrice = 0;
            foreach ($order->orderServices as $key => $orderService) {
                $servicesPrice += $orderService->getFinalPrice();
            }

            echo "price: {$order->price} | servicesPrice: {$servicesPrice}\n";

            if ($order->price != $servicesPrice) {
                $order->updateAttributes(['price' => $servicesPrice]);
            }
        }
    }

    public function safeDown()
    {
    }

}
