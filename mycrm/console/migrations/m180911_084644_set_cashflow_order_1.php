<?php

use core\models\company\Company;
use core\models\finance\CompanyCashflow;
use yii\db\Migration;

/**
 * Class m180911_084644_parse_cashflow_transactions
 */
class m180911_084644_set_cashflow_order_1 extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        /* @var Company[] $companies */
        $companies = Company::find()
            ->andWhere(['status' => Company::STATUS_ENABLED])
            // ->andWhere(['id' => 205])
            ->orderBy(['id' => SORT_ASC])
            ->all();
        $companies_count = count($companies) - 1;

        foreach ($companies as $index => $company) {
            echo "-------- {$index} of {$companies_count} | {$company->name}\n";
            $this->updateCashflows($company);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
    }

    /**
     * Set order_id via order relation
     */
    private function updateCashflows(Company $company)
    {
        $cashflows = CompanyCashflow::find()
            ->company($company->id)
            ->andWhere(['order_id' => null])
            ->orderBy(['created_at' => SORT_DESC]);
        $all = $cashflows->count();
        $i = 1;
        foreach ($cashflows->each() as $cashflow) {
            echo $i++ . " out of {$all} \n";

            $orderCashflow = (new \yii\db\Query())
                ->from('{{%company_cashflow_orders}}')
                ->where(['{{%company_cashflow_orders}}.cashflow_id' => $cashflow->id])
                ->one();

            if (empty($orderCashflow)) {
                continue;
            }

            $this->update(
                CompanyCashflow::tableName(),
                ['order_id' => $orderCashflow['order_id']],
                ['id' => $cashflow->id, 'order_id' => null]
            );
        }
    }
}
