<?php

use core\models\company\Company;
use core\models\finance\CompanyCashflow;
use core\models\finance\CompanyCostItem;
use core\models\order\Order;
use yii\db\Migration;

/**
 * Class m180911_084644_parse_cashflow_transactions
 */
class m180911_084644_set_cashflow_order_3 extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        /* @var Company[] $companies */
        $companies = Company::find()
            ->andWhere(['status' => Company::STATUS_ENABLED])
            ->orderBy(['id' => SORT_ASC])
            // ->andWhere(['id' => 205])
            ->all();
        $companies_count = count($companies) - 1;

        foreach ($companies as $index => $company) {
            echo "-------- {$index} of {$companies_count} | {$company->name}\n";
            $this->parseOrders($company);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
    }

    /**
     * Set order_id by finding order number in comment
     */
    private function parseOrders(Company $company)
    {
        $cashflows = CompanyCashflow::find()
            ->company($company->id)
            ->andWhere(['order_id' => null])
            ->andWhere(['like', 'comment', 'Запись']);

        foreach ($cashflows->each() as $cashflow) {
            /* @var CompanyCashflow $cashflow */
            $order_number = substr($cashflow->comment, 14, strpos($cashflow->comment, "\": ") - 14);

            if (is_numeric($order_number)) {
                $order = Order::find()
                    ->company(true, $company->id)
                    ->andWhere([
                        'number' => $order_number,
                        'staff_id' => $cashflow->staff_id,
                        'company_customer_id' => $cashflow->customer_id
                    ])
                    ->one();
                if ($order) {
                    $this->update(CompanyCashflow::tableName(),
                        ['order_id' => $order->id],
                        ['id' => $cashflow->id, 'order_id' => null]
                    );
                } else {
                    $message = "Order: {$order_number} is not found\n".
                    "{$cashflow->company_id} | {$cashflow->comment}\n";
                    echo $message;
                }
            } else {
                echo "$order_number {$cashflow->comment} \n";
            }
        }
    }
}
