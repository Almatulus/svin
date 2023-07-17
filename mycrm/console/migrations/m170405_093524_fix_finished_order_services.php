<?php

use core\helpers\order\OrderConstants;
use yii\db\Migration;

class m170405_093524_fix_finished_order_services extends Migration
{
    public function safeUp()
    {
        $orderServices = \core\models\order\OrderService::find()->joinWith(['divisionService', 'order'])->all();

        foreach ($orderServices as $orderService) {
            /* @var \core\models\order\OrderService $orderService */
            if ($orderService->order->status != OrderConstants::STATUS_ENABLED
                && $orderService->price == 0
                && $orderService->order->price != 0
            ) {
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
