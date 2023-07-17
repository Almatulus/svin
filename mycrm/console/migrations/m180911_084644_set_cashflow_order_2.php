<?php

use core\models\company\Company;
use core\models\finance\CompanyCashflow;
use core\models\finance\CompanyCostItem;
use core\models\order\Order;
use yii\db\Migration;

/**
 * Class m180911_084644_parse_cashflow_transactions
 */
class m180911_084644_set_cashflow_order_2 extends Migration
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
            $this->updateDeposit($company);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
    }

    /**
     * Set order_id by finding order cashflow with same created_at
     */
    private function updateDeposit(Company $company)
    {
        $cashflows = CompanyCashflow::find()
            ->company($company->id)
            ->joinWith('costItem')
            ->andWhere([
                '{{%company_cost_items}}.cost_item_type' => [
                    CompanyCostItem::COST_ITEM_TYPE_DEPOSIT_INCOME,
                    CompanyCostItem::COST_ITEM_TYPE_DEPOSIT_EXPENSE
                ],
                'order_id'                               => null,
            ])
            ->andWhere(['not', ['customer_id' => null]]);

        $cashflow_count = $cashflows->count();

        $i = 0;
        foreach ($cashflows->each() as $cashflow) {
            /* @var CompanyCashflow $orderCashflow */
            /* @var CompanyCashflow $cashflow */
            $orderCashflow = CompanyCashflow::find()
                ->company($cashflow->company_id)
                ->andWhere([
                    'customer_id' => $cashflow->customer_id,
                    'cash_id' => $cashflow->cash_id
                ])
                ->andWhere([
                    'AND',
                    ['>=', 'created_at', $cashflow->created_at - 1],
                    ['<=', 'created_at', $cashflow->created_at + 1],
                ])
                ->andWhere(['not', ['order_id' => null]])
                ->orderBy(['id' => SORT_ASC])
                ->one();

            $i++;
            if ($orderCashflow) {
                $this->update(CompanyCashflow::tableName(),
                    ['order_id' => $orderCashflow->order_id],
                    ['id' => $cashflow->id, 'order_id' => null]
                );
            }
            echo "{$i} of {$cashflow_count} \n";
        }
    }
}
