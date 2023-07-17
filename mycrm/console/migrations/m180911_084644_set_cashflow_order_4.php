<?php

use core\models\company\Company;
use core\models\finance\CompanyCashflow;
use core\models\finance\CompanyCostItem;
use core\models\order\Order;
use yii\db\Migration;

/**
 * Class m180911_084644_parse_cashflow_transactions
 */
class m180911_084644_set_cashflow_order_4 extends Migration
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
     * Set order_id for COST_ITEM_TYPE_SERVICE type by looking for orders
     * with same customer_id, staff_id, cash_id and total_price
     */
    private function parseOrders(Company $company)
    {
        $cashflows = CompanyCashflow::find()
            ->company($company->id)
            ->andWhere(['order_id' => null])
            ->andWhere(['NOT', ['customer_id' => null]])
            ->andWhere(['NOT', ['staff_id' => null]])
            ->andWhere(['NOT', ['cash_id' => null]])
            ->joinWith(['costItem'])
            ->andWhere(['cost_item_type' => CompanyCostItem::COST_ITEM_TYPE_SERVICE]);

        foreach ($cashflows->each() as $cashflow) {
            /* @var CompanyCashflow $cashflow */
            /* @var Order $order */
            $order = Order::find()
                ->andWhere([
                    'staff_id'            => $cashflow->staff_id,
                    'company_customer_id' => $cashflow->customer_id,
                    'company_cash_id'     => $cashflow->cash_id,
                    'datetime'            => $cashflow->date
                ])
                ->andWhere([
                    '<',
                    'created_time',
                    date('Y-m-d H:i:s', $cashflow->created_at)
                ])
                ->orderBy(['datetime' => SORT_ASC])
                ->one();

            if (!$order) {
                continue;
            }

            $this->update(CompanyCashflow::tableName(),
                ['order_id' => $order->id],
                ['id' => $cashflow->id, 'order_id' => null]
            );
        }
    }
}
