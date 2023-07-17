<?php

use core\models\finance\CompanyCash;
use core\helpers\order\OrderConstants;
use core\models\order\Order;
use yii\db\Migration;

class m170528_175550_alter_order_company_cash extends Migration
{
    public function safeUp()
    {
        $orders_updated = 0;
        $orders = Order::find()
            ->joinWith(['companyCash', 'staff'])
            ->orderBy('id')
            ->andWhere(['{{%orders}}.status' => OrderConstants::STATUS_ENABLED]);

        foreach ($orders->each() as $order) {
            /* @var Order $order */
            if ($order->companyCash->division_id === $order->staff->division_id) {
                 continue;
            }

            /* @var CompanyCash $companyCash */
            $companyCash = CompanyCash::find()->where(['division_id' => $order->staff->division_id])->one();

            echo "Order: {$order->companyCustomer->company->name} {$order->datetime} {$order->key} cash: {$order->companyCash->division_id} staff: {$order->staff->division_id}";

            if ($companyCash === null) {
                echo " skipped\n";
            } else {
                $orders_updated++;
                echo " updated from {$order->company_cash_id} to {$companyCash->id}\n";
                $order->updateAttributes(['company_cash_id' => $companyCash->id]);
            }
        }
        echo $orders_updated . " orders updated \n";
    }

    public function safeDown()
    {
    }
}
