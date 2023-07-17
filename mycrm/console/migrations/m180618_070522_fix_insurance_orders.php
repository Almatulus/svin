<?php

use core\models\order\Order;
use yii\db\Migration;

/**
 * Class m180618_070522_fix_insurance_orders
 */
class m180618_070522_fix_insurance_orders extends Migration
{
    private $company_id = 127;

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $orders = Order::find()->company(false, $this->company_id)
            ->finished()
            ->andWhere('{{%orders}}.insurance_company_id IS NOT NULL')
            ->startFrom(new DateTime("2018-01-01"))
            ->to(new DateTime("2018-03-31"));

        $insurancePayment = \core\models\Payment::findOne(['type' => \core\helpers\company\PaymentHelper::INSURANCE]);

        foreach ($orders->each(100) as $order) {
            /** @var Order $order */
//            if (sizeof($order->orderPayments) > 1) {
//                echo "order {$order->id} has more than one payment";
//                continue;
//            }

            \core\models\order\OrderPayment::updateAll(['payment_id' => $insurancePayment->id],
                ['order_id' => $order->id]);

            foreach ($order->cashflows as $cashflow) {
//                if (sizeof($cashflow->payments) > 1) {
//                    echo "cashflow {$cashflow->id} has more than one payment";
//                    continue;
//                }
                $cashflow->updateAttributes(['value' => 0]);
                \core\models\finance\CompanyCashflowPayment::updateAll(['payment_id' => $insurancePayment->id],
                    ['cashflow_id' => $cashflow->id]);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $orders = Order::find()->company(false, $this->company_id)->finished()
            ->andWhere('{{%orders}}.insurance_company_id IS NOT NULL')
            ->startFrom(new DateTime("2018-01-01"))
            ->to(new DateTime("2018-03-31"));

        foreach ($orders->each(100) as $order) {
            /** @var Order $order */
//            if (sizeof($order->orderPayments) > 1) {
//                echo "order {$order->id} has more than one payment";
//                continue;
//            }

            \core\models\order\OrderPayment::updateAll(['payment_id' => \core\helpers\company\PaymentHelper::CASH_ID],
                ['order_id' => $order->id]);

            foreach ($order->cashflows as $cashflow) {
//                if (sizeof($cashflow->payments) > 1) {
//                    echo "cashflow {$cashflow->id} has more than one payment";
//                    continue;
//                }
                \core\models\finance\CompanyCashflowPayment::updateAll(['payment_id' => \core\helpers\company\PaymentHelper::CASH_ID],
                    ['cashflow_id' => $cashflow->id]);
            }
        }
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180618_070522_fix_insurance_orders cannot be reverted.\n";

        return false;
    }
    */
}
