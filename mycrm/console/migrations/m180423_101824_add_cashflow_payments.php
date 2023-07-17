<?php

use core\models\finance\CompanyCashflow;
use yii\db\Migration;

/**
 * Class m180423_101824_add_cashflow_payments
 */
class m180423_101824_add_cashflow_payments extends Migration
{
    private $estimatedOrders;

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%company_cashflow_payments}}', [
            'cashflow_id' => $this->integer()->unsigned()->notNull(),
            'payment_id'  => $this->integer()->unsigned()->notNull(),
            'value'       => $this->integer()->unsigned()->notNull()
        ]);

        $this->addPrimaryKey($this->db->tablePrefix . 'company_cashflow_payments_pkey',
            '{{%company_cashflow_payments}}',
            ['cashflow_id', 'payment_id']);

        $this->addForeignKey('fk_cashflow_payments_cashflow', '{{%company_cashflow_payments}}', 'cashflow_id',
            '{{%company_cashflows}}', 'id');
        $this->addForeignKey('fk_cashflow_payments_payment', '{{%company_cashflow_payments}}', 'payment_id',
            '{{%payments}}', 'id');

        $cashflows = \core\models\finance\CompanyCashflow::find()
//            ->andWhere(['company_id' => 131])
//            ->range((new DateTime())->modify("-1 month")->format("Y-m-d"), (new DateTime())->format("Y-m-d"))
//            ->active()
            ->orderBy('created_at DESC');

        $counter = 0;
        foreach ($cashflows->each(100) as $cashflow) {
            echo "$cashflow->id | ";

            /** @var CompanyCashflow $cashflow */
            if ($cashflow->cashflowProduct || $cashflow->cashflowService) {

//                $order = $cashflow->getOrder();
                if ($cashflow->cashflowProduct) {
                    $order = $cashflow->cashflowProduct->orderProduct->order;
                } elseif ($cashflow->cashflowService) {
                    $order = $cashflow->cashflowService->orderService->order;
                }

                if (!isset($this->estimatedOrders[$order->id])) {
                    $this->estimatedOrders[$order->id] = [
                        'isLastCheckout' => $cashflow->costItem->isIncome(),
                        'cash'           => 0,
                        'not_cash'       => ['total' => 0, 'details' => []]
                    ];

                    foreach ($order->orderPayments as $orderPayment) {
                        if (!$orderPayment->payment->isDeposit() && !$orderPayment->payment->isInsurance()) {
                            if ($orderPayment->payment_id == 1) {
                                $this->estimatedOrders[$order->id]['cash'] += $orderPayment->amount;
                            } else {
                                $this->estimatedOrders[$order->id]['not_cash']['total'] += $orderPayment->amount;
                                if (!isset($this->estimatedOrders[$order->id]['not_cash'][$orderPayment->payment_id])) {
                                    $this->estimatedOrders[$order->id]['not_cash']['details'][$orderPayment->payment_id] = 0;
                                }
                                $this->estimatedOrders[$order->id]['not_cash']['details'][$orderPayment->payment_id] += $orderPayment->amount;
                            }
                        }
                    }
                }

                // check if last transaction for order was checkout
                if ($this->estimatedOrders[$order->id]['isLastCheckout']) {

                    if ($this->estimatedOrders[$order->id]['not_cash']['total'] > 0) {

                        $value = min($this->estimatedOrders[$order->id]['not_cash']['total'], $cashflow->value);
                        $this->estimatedOrders[$order->id]['not_cash']['total'] -= $value;
                        $cashflow->value -= $value;

                        foreach ($this->estimatedOrders[$order->id]['not_cash']['details'] as $payment_id => $amount) {
                            if ($value <= 0 || $amount <= 0) {
                                continue;
                            }
                            $paymentValue = min($amount, $value);
                            $value -= $paymentValue;
                            $this->estimatedOrders[$order->id]['not_cash']['details'][$payment_id] -= $paymentValue;

                            $this->insert('{{%company_cashflow_payments}}', [
                                'cashflow_id' => $cashflow->id,
                                'payment_id'  => $payment_id,
                                'value'       => $paymentValue
                            ]);

                            $counter++;
                        }
                    }

                    if ($cashflow->value > 0) {
                        $value = min($this->estimatedOrders[$order->id]['cash'], $cashflow->value);
                        $this->estimatedOrders[$order->id]['cash'] -= $value;

                        $this->insert('{{%company_cashflow_payments}}', [
                            'cashflow_id' => $cashflow->id,
                            'payment_id'  => 1,
                            'value'       => $cashflow->value
                        ]);

                        $counter++;
                    }

                } else {
                    $this->insert('{{%company_cashflow_payments}}', [
                        'cashflow_id' => $cashflow->id,
                        'payment_id'  => 1,
                        'value'       => $cashflow->value
                    ]);
                    $counter++;
                }
            } else {
                $this->insert('{{%company_cashflow_payments}}', [
                    'cashflow_id' => $cashflow->id,
                    'payment_id'  => 1,
                    'value'       => $cashflow->value
                ]);
                $counter++;
            }
        }

        echo PHP_EOL . " {$counter} records inserted";
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%company_cashflow_payments}}');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180423_101824_add_cashflow_payments cannot be reverted.\n";

        return false;
    }
    */
}
