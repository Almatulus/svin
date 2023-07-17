<?php

use core\helpers\order\OrderConstants;
use core\models\order\Order;
use yii\db\Migration;

class m170803_114227_fix_cashflow extends Migration
{
    public function safeUp()
    {
        $ordersQuery = Order::find()
            ->where(['status' => OrderConstants::STATUS_FINISHED])
            ->orderBy(['id' => SORT_ASC]);
        $i           = 0;
        foreach ($ordersQuery->each() as $order) {
            /* @var Order $order */
            $orderServices = $order->orderServices;
            $orderProducts = $order->orderProducts;
            $serviceCount = count($orderServices);
            $productCount = count($orderProducts);
            $orderPayment = $total = $order->getOrderPayments()->sum('amount');

            if ($orderPayment == 0) {
                continue;
            }

            // Services
            $s = 0;
            foreach ($orderServices as $orderService) {
                $s++;
                $cashflowService = $orderService->companyCashflowService;
                if ($cashflowService == null) {
                    continue;
                }

                $companyCashflow = $cashflowService->cashflow;
                $price = min($orderPayment, $companyCashflow->value);

                if ($price !== $companyCashflow->value) {
                    echo "Order Time: {$order->datetime} | Company: {$order->staff->division->company->name} | Services: {$serviceCount} |  Price: {$order->price} | Payment {$orderPayment} | Total {$total} \n";
                    echo "-- {$s}) Name: {$orderService->divisionService->service_name} | Price: {$companyCashflow->value} changed to {$price}\n";
                    $i++;
                }

                $companyCashflow->value = $price;
                if ($companyCashflow->update(false) === false) {
                    throw new Exception(json_encode($companyCashflow->getErrors()));
                }

                $orderPayment -= $price;
            }

            // Products
            $p=0;
            foreach ($orderProducts as $orderProduct) {
                $p++;
                $cashflowProduct = $orderProduct->companyCashflowProduct;
                if ($cashflowProduct == null) {
                    continue;
                }

                $companyCashflow = $cashflowProduct->cashflow;
                $price = min($orderPayment, $companyCashflow->value);

                if ($price !== $companyCashflow->value) {
                    echo "Order Time: {$order->datetime} | Company: {$order->staff->division->company->name} | Services: {$productCount} | Price: {$order->price} | Payment {$orderPayment} | Total {$total} \n";
                    echo "-- {$p}) Name: {$orderProduct->product->name} | Price: {$companyCashflow->value} changed to {$price}\n";
                    $i++;
                }

                $companyCashflow->value = $price;
                if ($companyCashflow->update(false) === false) {
                    throw new Exception(json_encode($companyCashflow->getErrors()));
                }

                $orderPayment -= $price;
            }
        }

        echo "$i cashflows changed \n";
    }

    public function safeDown()
    {
    }
}
