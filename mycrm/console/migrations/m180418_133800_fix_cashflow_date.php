<?php

use core\models\finance\CompanyCashflow;
use core\models\finance\CompanyCostItem;
use yii\db\Migration;

/**
 * Class m180418_133800_fix_cashflow_date
 */
class m180418_133800_fix_cashflow_date extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        /* @var CompanyCashflow[] $cashflows */
        $cashflows = CompanyCashflow::find()
            ->joinWith('costItem')
            ->where(['{{%company_cost_items}}.cost_item_type' => CompanyCostItem::COST_ITEM_TYPE_REFUND])
            ->all();

        foreach ($cashflows as $cashflow) {
            if ($cashflow->cashflowProduct) {
                $datetime = $cashflow->cashflowProduct->orderProduct->order->datetime;
            } elseif ($cashflow->cashflowService) {
                $datetime = $cashflow->cashflowService->orderService->order->datetime;
            }

            $this->update(CompanyCashflow::tableName(), ['date' => $datetime], ['id' => $cashflow->id]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {

    }
}
