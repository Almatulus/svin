<?php

use yii\db\Migration;

class m170331_215241_fix_order_service_price extends Migration
{
    public function safeUp()
    {
        $orderServices = \core\models\order\OrderService::find()->joinWith('divisionService')->all();

        foreach ($orderServices as $orderService) {
            /* @var \core\models\order\OrderService $orderService */
            if ($orderService->order->status == \core\helpers\order\OrderConstants::STATUS_ENABLED) {
                $orderService->price = $orderService->divisionService->price;
                $orderService->save();
                echo "Order service: {$orderService->id} \n";
            }
        }
    }

    public function safeDown()
    {
    }
}
