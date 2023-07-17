<?php

use core\models\customer\CompanyCustomer;
use core\helpers\order\OrderConstants;
use core\models\order\Order;
use yii\db\Migration;

class m170824_203304_recalculate_order_debts extends Migration
{
    public function safeUp()
    {
        $companyCustomers = CompanyCustomer::find()
            ->innerJoinWith('orders')
            ->andWhere(['!=', '{{%orders}}.payment_difference', 0])
            ->orderBy('company_id');

        foreach ($companyCustomers->each() as $model) {
            /* @var CompanyCustomer $model */
            $deposit    = intval($model->getOrders()
                ->where(['status' => OrderConstants::STATUS_FINISHED])
                ->andWhere(['>', 'payment_difference', 0])
                ->sum('payment_difference'));
            $debt = intval($model->getOrders()
                ->where(['status' => OrderConstants::STATUS_FINISHED])
                ->andWhere(['<', 'payment_difference', 0])
                ->sum('payment_difference'));

            if ($debt === 0 || $deposit === 0) {
                continue;
            }

            echo $model->id;
            echo " ";
            echo $model->company->name;
            echo " ";
            echo $model->customer->phone;
            echo " ";
            echo $model->balance;
            echo " ";
            echo $debt;
            echo " ";
            echo $deposit;
            echo " ";
            echo $deposit + $debt;
            echo "\n";

            if (abs($debt) === $deposit) {
                $this->update(Order::tableName(), ['payment_difference' => 0], ['company_customer_id' => $model->id]);
                continue;
            }

            if (abs($debt) > $deposit) {
                // Clear all deposit orders
                $this->update(Order::tableName(), ['payment_difference' => 0], [
                    'and',
                    ['company_customer_id' => $model->id],
                    ['>', 'payment_difference', 0]
                ]);
                /* @var $debtOrders Order[] */
                $debtOrders = $model->getOrders()
                    ->where(['status' => OrderConstants::STATUS_FINISHED])
                    ->andWhere(['<', 'payment_difference', 0])
                    ->orderBy(['datetime' => SORT_DESC])
                    ->all();
                $totalDebt = abs($deposit + $debt);
                foreach ($debtOrders as $order) {
                    $payment_difference = min(abs($order->payment_difference), $totalDebt);
                    $order->updateAttributes([
                        'payment_difference' => $payment_difference * -1
                    ]);
                    $totalDebt = $totalDebt - $payment_difference;
                }
                continue;
            }

            // Clear all debt orders
            $this->update(Order::tableName(), ['payment_difference' => 0], [
                'and',
                ['company_customer_id' => $model->id],
                ['<', 'payment_difference', 0]
            ]);
            /* @var $depositOrders Order[] */
            $depositOrders = $model->getOrders()
                ->where(['status' => OrderConstants::STATUS_FINISHED])
                ->andWhere(['>', 'payment_difference', 0])
                ->orderBy(['datetime' => SORT_DESC])
                ->all();
            $totalDeposit = abs($deposit + $debt);
            foreach ($depositOrders as $order) {
                $payment_difference = min($order->payment_difference, $totalDeposit);
                $order->updateAttributes([
                    'payment_difference' => $payment_difference
                ]);
                $totalDeposit = $totalDeposit - $payment_difference;
            }
        }
    }

    public function safeDown()
    {
    }
}
