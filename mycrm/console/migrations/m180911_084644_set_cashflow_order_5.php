<?php

use core\models\company\Company;
use core\models\finance\CompanyCashflow;
use core\models\finance\CompanyCostItem;
use core\models\order\Order;
use yii\db\Migration;

/**
 * Class m180911_084644_parse_cashflow_transactions
 */
class m180911_084644_set_cashflow_order_5 extends Migration
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
     */
    private function updateCashflows(Company $company)
    {
        $cashflows = CompanyCashflow::find()
            ->company($company->id)
            ->joinWith('order')
            ->andWhere([CompanyCashflow::tableName().'.staff_id' => null])
            ->andWhere(['NOT', [CompanyCashflow::tableName().'.order_id' => null]]);

        foreach ($cashflows->each() as $cashflow) {
            /* @var CompanyCashflow $cashflow */
            $this->update(CompanyCashflow::tableName(),
                ['staff_id' => $cashflow->order->staff_id],
                ['id' => $cashflow->id, 'staff_id' => null]
            );
        }
    }
}
