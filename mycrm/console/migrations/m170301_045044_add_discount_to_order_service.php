<?php

use core\models\order\Order;
use core\models\order\OrderService;
use yii\db\Migration;

class m170301_045044_add_discount_to_order_service extends Migration
{
    // public function up()
    // {

    // }

    // public function down()
    // {
    //     echo "m170301_045044_add_discount_to_order_service cannot be reverted.\n";

    //     return false;
    // }

    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
        $this->addColumn('{{%order_services}}', 'discount', $this->integer()->defaultValue(0));

        echo "--- Start price estimation ---";
        foreach (Order::find()->each() as $order) {
            $price = $order->price * (100 - $order->discount) / 100;
            echo "Order {$order->id} oldPrice = {$order->price} discount = {$order->discount} newPrice= {$price}\n";
            OrderService::updateAll(['discount' => $order->discount], ['order_id' => $order->id]);
            $order->updateAttributes(['price' => $price]);
        }
        echo "--- End price estimation ---";
    }

    public function safeDown()
    {
        echo "--- Start price estimation ---";
        foreach (Order::find()->each() as $order) {
            $price = $order->price * 100 / (100 - $order->discount);
            echo "Order {$order->id} oldPrice = {$order->price} discount = {$order->discount} newPrice= {$price}\n";
            $order->updateAttributes(['price' => $price]);
        }
        echo "--- End price estimation ---";

        $this->dropColumn('{{%order_services}}', 'discount');
    }
    
}
