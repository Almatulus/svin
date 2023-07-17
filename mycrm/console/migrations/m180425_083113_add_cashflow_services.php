<?php

use core\models\finance\CompanyCashflow;
use core\models\order\Order;
use core\models\order\OrderService;
use yii\db\Migration;

/**
 * Class m180425_083113_add_cashflow_services
 */
class m180425_083113_add_cashflow_services extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%cashflow_services}}', [
            'id'          => $this->primaryKey(),
            'cashflow_id' => $this->integer()->unsigned()->notNull(),
            'service_id'  => $this->integer()->unsigned()->notNull(),
            'discount'    => $this->integer()->unsigned()->defaultValue(0),
            'price'       => $this->integer()->unsigned()->notNull(),
            'quantity'    => $this->integer()->unsigned()->notNull()
        ]);

//        $this->addPrimaryKey($this->db->tablePrefix . 'cashflow_services_pkey',
//            '{{%cashflow_services}}',
//            ['cashflow_id', 'service_id']);

        $this->addForeignKey('fk_cashflow_payments_cashflow', '{{%cashflow_services}}', 'cashflow_id',
            '{{%company_cashflows}}', 'id');
        $this->addForeignKey('fk_cashflow_payments_service', '{{%cashflow_services}}', 'service_id',
            '{{%division_services}}', 'id');

        $this->createTable('{{%cashflow_products}}', [
            'id'          => $this->primaryKey(),
            'cashflow_id' => $this->integer()->unsigned()->notNull(),
            'product_id'  => $this->integer()->unsigned()->notNull(),
            'discount'    => $this->integer()->unsigned()->defaultValue(0),
            'price'       => $this->integer()->unsigned()->notNull(),
            'quantity'    => $this->integer()->unsigned()->notNull()
        ]);

//        $this->addPrimaryKey($this->db->tablePrefix . 'cashflow_products_pkey',
//            '{{%cashflow_products}}',
//            ['cashflow_id', 'product_id']);

        $this->addForeignKey('fk_cashflow_products_cashflow', '{{%cashflow_products}}', 'cashflow_id',
            '{{%company_cashflows}}', 'id');
        $this->addForeignKey('fk_cashflow_products_product', '{{%cashflow_products}}', 'product_id',
            '{{%warehouse_product}}', 'id');

        $this->createTable('{{%cashflow_orders}}', [
            'cashflow_id' => $this->integer()->unsigned()->notNull(),
            'order_id'    => $this->integer()->unsigned()->notNull()
        ]);

        $this->addPrimaryKey($this->db->tablePrefix . 'cashflow_orders_pkey',
            '{{%cashflow_orders}}',
            ['cashflow_id', 'order_id']);

        $this->addForeignKey('fk_cashflow_orders_cashflow', '{{%cashflow_orders}}', 'cashflow_id',
            '{{%company_cashflows}}', 'id');
        $this->addForeignKey('fk_cashflow_orders_order', '{{%cashflow_orders}}', 'order_id',
            '{{%orders}}', 'id');

        $this->createTable('{{%cashflow_sales}}', [
            'cashflow_id' => $this->integer()->unsigned()->notNull(),
            'sale_id'     => $this->integer()->unsigned()->notNull()
        ]);

        $this->addPrimaryKey($this->db->tablePrefix . 'cashflow_sales_pkey',
            '{{%cashflow_sales}}',
            ['cashflow_id', 'sale_id']);

        $this->addForeignKey('fk_cashflow_sales_cashflow', '{{%cashflow_sales}}', 'cashflow_id',
            '{{%company_cashflows}}', 'id');
        $this->addForeignKey('fk_cashflow_sales_sale', '{{%cashflow_sales}}', 'sale_id',
            '{{%warehouse_sale}}', 'id');

        $subQuery = OrderService::find()
            ->select(['{{%order_services}}.id', '{{%order_services}}.order_id', 'COUNT(cs)'])
            ->innerJoinWith(['companyCashflowServices cs'])
            ->andWhere(['{{%order_services}}.deleted_time' => null])
            ->groupBy('{{%order_services}}.id, {{%order_services}}.order_id')/*->having('COUNT(cs) > 1')*/
        ;

        $this->softDeleteCashflows();

        $orders = Order::find()
            ->distinct()
            ->innerJoin(['os' => $subQuery], '{{%orders}}.id = os.order_id')
            ->orderBy('datetime DESC')
//            ->company(false, 131)
//            ->startFrom((new DateTime())->modify("-1 month"))
//            ->to(new DateTime())
//            ->limit(10000)
        ;

        $ordersCount = $orders->count();
//        $myfile = fopen("testfile.txt", "w");

        $hasDifferences = [];
        $hasTwoConsequentCheckouts = [];
        $counter = 0;
        foreach ($orders->each(1000) as $order) {
            /** @var Order $order */
//            fwrite($myfile, "order_id: {$order->id} | cash: {$order->company_cash_id} | customer_id: {$order->company_customer_id} | staff_id: {$order->staff_id}\n");
            echo "{$order->id} | " . (++$counter) . " | " . ($ordersCount - $counter) . PHP_EOL;

            $items = [];
            foreach ($order->getOrderServices()->where([])->joinWith('companyCashflows')->orderBy('updated_at DESC')->all() as $orderService) {
                $hasDifference = false;

                if (empty($orderService->companyCashflows)) {
                    continue;
                }

                $lastValue = $orderService->companyCashflows[0]->value;

                foreach ($orderService->companyCashflows as $companyCashflow) {

                    if ((!$order->isFinished() || $orderService->deleted_time != null) && $companyCashflow->date < "2018-04-18 00:00:00") {
                        continue;
                    }

                    if ($lastValue != $companyCashflow->value && !$hasDifference) {
                        $hasDifference = true;
                        $hasDifferences[] = $order->id;
                    }

                    $updated_at = substr(Yii::$app->formatter->asDatetime($companyCashflow->updated_at,
                        "php:Y-m-d H:i:s"), 0, -1);
                    $items[$companyCashflow->date][$updated_at][$companyCashflow->cost_item_id]['created_at'] = $companyCashflow->created_at;
                    $items[$companyCashflow->date][$updated_at][$companyCashflow->cost_item_id]['updated_at'] = $companyCashflow->updated_at;
                    $items[$companyCashflow->date][$updated_at][$companyCashflow->cost_item_id]['created_by'] = $companyCashflow->created_by;
                    $items[$companyCashflow->date][$updated_at][$companyCashflow->cost_item_id]['updated_by'] = $companyCashflow->updated_by;
                    $items[$companyCashflow->date][$updated_at][$companyCashflow->cost_item_id]['user_id'] = $companyCashflow->user_id;
                    $items[$companyCashflow->date][$updated_at][$companyCashflow->cost_item_id]['items'][] = [
                        'type'     => 'service',
                        'cashflow' => $companyCashflow,
                        'item'     => $orderService
                    ];
                }
            }

            $serviceCostItem = \core\models\finance\CompanyCostItem::find()->isService()->company($order->division->company_id)->one();

            foreach ($order->getOrderProducts()->where([])->joinWith('companyCashflows')->orderBy('updated_at DESC')->all() as $orderProduct) {
                $hasDifference = false;

                if (empty($orderProduct->companyCashflows)) {
                    continue;
                }

                $lastValue = $orderProduct->companyCashflows[0]->value;

                foreach ($orderProduct->companyCashflows as $companyCashflow) {

                    if ((!$order->isFinished() || $orderProduct->deleted_time != null) && $companyCashflow->date < "2018-04-18 00:00:00") {
                        continue;
                    }

                    if ($lastValue != $companyCashflow->value && !$hasDifference) {
                        $hasDifference = true;
                        $hasDifferences[] = $order->id;
                    }

                    $updated_at = substr(Yii::$app->formatter->asDatetime($companyCashflow->updated_at,
                        "php:Y-m-d H:i:s"), 0, -1);

                    $cost_item_id = $companyCashflow->cost_item_id;
                    if ($companyCashflow->costItem->isIncome()) {
                        $cost_item_id = $serviceCostItem->id;
                    }

                    $items[$companyCashflow->date][$updated_at][$cost_item_id]['created_at'] = $companyCashflow->created_at;
                    $items[$companyCashflow->date][$updated_at][$cost_item_id]['updated_at'] = $companyCashflow->updated_at;
                    $items[$companyCashflow->date][$updated_at][$cost_item_id]['created_by'] = $companyCashflow->created_by;
                    $items[$companyCashflow->date][$updated_at][$cost_item_id]['updated_by'] = $companyCashflow->updated_by;
                    $items[$companyCashflow->date][$updated_at][$cost_item_id]['user_id'] = $companyCashflow->user_id;
                    $items[$companyCashflow->date][$updated_at][$cost_item_id]['items'][] = [
                        'type'     => 'product',
                        'cashflow' => $companyCashflow,
                        'item'     => $orderProduct
                    ];
                }
            }

            foreach ($items as $date => $ds) {
//                fwrite($myfile, "\t{$date}\n");
                $lastCostItemId = null;

                ksort($ds);

                foreach ($ds as $updated_at => $crs) {

//                    fwrite($myfile, "\t\t{$updated_at}\n");
                    foreach ($crs as $cost_item_id => $cashflowsData) {

                        if (empty($cashflowsData['items'])) {
                            continue;
                        }

                        if ($lastCostItemId !== null && $lastCostItemId == $cost_item_id) {
                            $hasTwoConsequentCheckouts[$order->id] = true;
//                            break;
                        }

                        $lastCostItemId = $cost_item_id;

//                        fwrite($myfile, "\t\t\t{$cost_item_id}\n");

                        $orderCashflow = CompanyCashflow::add($date, $order->company_cash_id,
                            "Оплата за запись № {$order->number}",
                            $order->division->company_id, null, $cost_item_id, $order->company_customer_id,
                            $order->division_id,
                            CompanyCashflow::RECEIVER_STAFF, $order->staff_id, 0, $cashflowsData['user_id']);
                        $orderCashflow->detachBehaviors();
                        $orderCashflow->created_at = $cashflowsData['created_at'];
                        $orderCashflow->updated_at = $cashflowsData['updated_at'];
                        $orderCashflow->created_by = $cashflowsData['created_by'];
                        $orderCashflow->updated_by = $cashflowsData['updated_by'];

                        $orderCashflow->save(false);

                        $totalValue = 0;
                        $lastStaffId = $order->staff_id;
                        $lastDivisionId = $order->division_id;
                        $lastCashId = $order->company_cash_id;
                        $lastCustomerId = $order->company_customer_id;

                        $payments = [];
                        foreach ($cashflowsData['items'] as $cashflowData) {

                            $totalValue += $cashflowData['cashflow']->value;

                            if ($cashflowData['cashflow']->costItem->isIncome() && $cashflowData['type'] == 'service') {
                                $cashflowService = new \core\models\finance\CompanyCashflowService([
                                    'cashflow_id' => $orderCashflow->id,
                                    'service_id'  => $cashflowData['item']->division_service_id,
                                    'discount'    => $cashflowData['item']->discount,
                                    'price'       => $cashflowData['item']->price,
                                    'quantity'    => $cashflowData['item']->quantity
                                ]);
                                $cashflowService->save(false);
                            }

                            if ($cashflowData['cashflow']->costItem->isIncome() && $cashflowData['type'] == 'product') {
                                $cashflowProduct = new \core\models\finance\CompanyCashflowProduct([
                                    'cashflow_id' => $orderCashflow->id,
                                    'product_id'  => $cashflowData['item']->product_id,
                                    'price'       => $cashflowData['item']->selling_price,
                                    'quantity'    => $cashflowData['item']->quantity
                                ]);
                                $cashflowProduct->save(false);
                            }

                            foreach ($cashflowData['cashflow']->payments as $payment) {
                                $payments[$payment->payment_id] = !isset($payments[$payment->payment_id]) ? $payment->value : ($payments[$payment->payment_id] + $payment->value);
                            }

                            $lastCashId = $cashflowData['cashflow']->cash_id;
                            $lastCustomerId = $cashflowData['cashflow']->customer_id;
                            $lastDivisionId = $cashflowData['cashflow']->division_id;
                            $lastStaffId = $cashflowData['cashflow']->staff_id;

                            $cashflowData['cashflow']->updateAttributes([
                                'is_deleted' => true,
                                'status'     => CompanyCashflow::STATUS_INACTIVE
                            ]);

//                            fwrite($myfile,
//                                "\t\t\t\t{$cashflowData['cashflow']->costItem->name} | "
//                                . "{$cashflowData['cashflow']->comment} | "
//                                . "{$cashflowData['cashflow']->value} | "
//                                . "cash: {$cashflowData['cashflow']->cash_id} | "
//                                . "customer: {$cashflowData['cashflow']->customer_id} | "
//                                . "staff: {$cashflowData['cashflow']->staff_id}\n");
                        }

                        $orderCashflow->updateAttributes([
                            'value'       => $totalValue,
                            'cash_id'     => $lastCashId,
                            'customer_id' => $lastCustomerId,
                            'division_id' => $lastDivisionId,
                            'staff_id'    => $lastStaffId
                        ]);

                        foreach ($payments as $payment_id => $value) {
                            $cashflowPayment = new \core\models\finance\CompanyCashflowPayment([
                                'cashflow_id' => $orderCashflow->id,
                                'payment_id'  => $payment_id,
                                'value'       => $value
                            ]);
                            $cashflowPayment->save(false);
                        }

                        $orderCashflow->link('order', $order);
                    }
                }
            }
        }

        echo sizeof($hasTwoConsequentCheckouts) . " records has two consequent checkouts" . PHP_EOL;
        foreach ($hasTwoConsequentCheckouts as $order_id => $val) {
            echo $order_id . PHP_EOL;
        }

//        echo sizeof($hasDifferences) . " records has difference in transactions";
//        foreach ($hasDifferences as $order_id) {
//            echo $order_id . PHP_EOL;
//        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $subQuery = OrderService::find()
            ->select(['{{%order_services}}.id', '{{%order_services}}.order_id', 'COUNT(cs)'])
            ->innerJoinWith(['companyCashflowServices cs'])
            ->andWhere(['{{%order_services}}.deleted_time' => null])
            ->groupBy('{{%order_services}}.id, {{%order_services}}.order_id')/*->having('COUNT(cs) > 1')*/
        ;

        $orders = Order::find()
            ->distinct()
            ->innerJoin(['os' => $subQuery], '{{%orders}}.id = os.order_id')
            ->orderBy('datetime DESC')
//            ->company(false, 131)
//            ->startFrom((new DateTime())->modify("-1 month"))
//            ->to(new DateTime())
//            ->limit(10000)
        ;

        $ordersCount = $orders->count();
        $counter = 0;
        foreach ($orders->each(1000) as $order) {
            echo "{$order->id} | " . (++$counter) . " | " . ($ordersCount - $counter) . PHP_EOL;

            foreach ($order->getOrderServices()->where([])->joinWith('companyCashflows')->orderBy('updated_at DESC')->all() as $orderService) {
                foreach ($orderService->companyCashflows as $companyCashflow) {
                    if ((!$order->isFinished() || $orderService->deleted_time != null) && $companyCashflow->date < "2018-04-18 00:00:00") {
                        continue;
                    }
                    $companyCashflow->updateAttributes([
                        'is_deleted' => false,
                        'status'     => CompanyCashflow::STATUS_ACTIVE
                    ]);
                }
            }
            foreach ($order->getOrderProducts()->where([])->joinWith('companyCashflows')->orderBy('updated_at DESC')->all() as $orderProduct) {
                foreach ($orderProduct->companyCashflows as $companyCashflow) {
                    if ((!$order->isFinished() || $orderProduct->deleted_time != null) && $companyCashflow->date < "2018-04-18 00:00:00") {
                        continue;
                    }
                    $companyCashflow->updateAttributes([
                        'is_deleted' => false,
                        'status'     => CompanyCashflow::STATUS_ACTIVE
                    ]);
                }
            }
        }

        $cashflow_ids = (new \yii\db\Query())->select(['cashflow_id'])->from('{{%cashflow_orders}}')->column();

        $this->dropTable('{{%cashflow_services}}');
        $this->dropTable('{{%cashflow_products}}');
        $this->dropTable('{{%cashflow_orders}}');
        $this->dropTable('{{%cashflow_sales}}');

        \core\models\finance\CompanyCashflowPayment::deleteAll(['cashflow_id' => $cashflow_ids]);
        CompanyCashflow::deleteAll(['id' => $cashflow_ids]);
    }

    private function softDeleteCashflows()
    {
        $deletedCashflows = CompanyCashflow::find()
            ->select('{{%company_cashflows}}.id')
            ->joinWith(['cashflowService.orderService', 'cashflowProduct.orderProduct'], false)
            ->where('{{%order_services}}.deleted_time IS NOT NULL OR {{%order_service_products}}.deleted_time IS NOT NULL')
            ->andWhere([
                'OR',
                ['{{%company_cashflows}}.status' => CompanyCashflow::STATUS_ACTIVE],
                ['{{%company_cashflows}}.is_deleted' => false]
            ])
            ->column();

        $this->update('{{%company_cashflows}}', [
            'is_deleted' => false,
            'status'     => CompanyCashflow::STATUS_INACTIVE
        ], ['id' => $deletedCashflows]);
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180425_083113_add_cashflow_services cannot be reverted.\n";

        return false;
    }
    */
}
