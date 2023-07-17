<?php

use core\models\finance\CompanyCostItem;
use core\models\order\Order;
use yii\db\Migration;

/**
 * Class m180614_102721_add_not_accountable_payments_to_cashflow
 */
class m180614_102721_add_not_accountable_payments_to_cashflow extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $company_id = 228;

        $orders = Order::find()
            ->finished()
            ->joinWith([
                'division',
                'orderPayments' => function (\core\models\order\query\OrderPaymentQuery $query) {
                    return $query->joinWith('payment', false)
                        ->andWhere(['{{%payments}}.type' => \core\helpers\company\PaymentHelper::notAccountable()])
                        ->andWhere(['>', '{{%order_payments}}.amount', 0]);
                }
            ])->andWhere([
                'not in',
                '{{%divisions}}.company_id',
                \core\helpers\order\OrderConstants::STATISTICS_EXCLUDED_COMPANIES
            ])
            ->orderBy('datetime ASC');

        $costItems = [];
        foreach ($orders->each(100) as $order) {
            /** @var Order $order */

            echo "{$order->datetime} | company_id = {$order->division->company->name} | company_id = {$order->division->company_id}" . PHP_EOL;

            $serviceCostItem = $costItems[$order->division->company_id] ?? CompanyCostItem::find()->company($order->division->company_id)->isService()->one();

            /** @var \core\models\finance\CompanyCashflow $lastCashflow */
            $lastCashflow = $order->getCashflows()->income()->costItem($serviceCostItem->id)
                ->orderBy('{{%company_cashflows}}.created_at DESC')->one();

            foreach ($order->orderPayments as $orderPayment) {
                if (!$orderPayment->payment->isAccountable()) {
                    $hasPayment = \core\models\finance\CompanyCashflowPayment::find()->andWhere(['payment_id' => $orderPayment->payment_id])->exists();

                    if (!$hasPayment) {
                        echo "\t{$orderPayment->payment->name} {$orderPayment->amount}" . PHP_EOL;
                        $newPayment = new \core\models\finance\CompanyCashflowPayment([
                            'payment_id' => $orderPayment->payment_id,
                            'cashflow_id' => $lastCashflow->id,
                            'value' => $orderPayment->amount
                        ]);
                        $newPayment->save();
                    } else {
                        if ($orderPayment->payment->isCertificate() && $order->division->company_id == $company_id) {
                            $lastCashflow->updateAttributes(['value' => $lastCashflow->value - $orderPayment->amount]);
                        }
                    }
                }
            }

        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {

    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180614_102721_add_not_accountable_payments_to_cashflow cannot be reverted.\n";

        return false;
    }
    */
}
