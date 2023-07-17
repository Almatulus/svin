<?php

use core\models\warehouse\Sale;
use yii\db\Migration;

/**
 * Class m180627_064408_parse_old_sales
 */
class m180627_064408_parse_old_sales extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $oldSales = Sale::find()
            ->distinct()
            ->joinWith('saleProducts')
            ->innerJoin('{{%sale_product_cashflow}}',
                '{{%warehouse_sale_product}}.id = {{%sale_product_cashflow}}.sale_product_id')
            ->orderBy('sale_date DESC');

        echo "Total: {$oldSales->count()}" . PHP_EOL;

        $saved = 0;
        foreach ($oldSales->each(100) as $sale) {
            /** @var Sale $sale */

//            echo "{$sale->id}" . PHP_EOL;

            $cashflow = new \core\models\finance\CompanyCashflow();
            $cashflow->detachBehaviors();

            $cashflowProducts = [];
            foreach ($sale->saleProducts as $pInd => $saleProduct) {
                $saleProductCashflow = \core\models\finance\CompanyCashflow::find()->innerJoin('{{%sale_product_cashflow}}',
                    '{{%sale_product_cashflow}}.cashflow_id = {{%company_cashflows}}.id')
                    ->andWhere(['sale_product_id' => $saleProduct->id])->one();
                if ($pInd == 0) {
                    $cashflow->attributes = $saleProductCashflow->attributes;
                    $cashflow->updated_by = $saleProductCashflow->updated_by;
                } else {
                    if ($saleProductCashflow) {
                        $cashflow->value += $saleProductCashflow->value;
                    }
                }

                $cashflowProducts[] = new \core\models\finance\CompanyCashflowProduct([
                    'product_id' => $saleProduct->product_id,
                    'price'      => $saleProduct->price,
                    'quantity'   => $saleProduct->quantity
                ]);

                if ($saleProductCashflow) {
                    $saleProductCashflow->updateAttributes([
                        'status'     => \core\models\finance\CompanyCashflow::STATUS_INACTIVE,
                        'is_deleted' => true
                    ]);
                }
            }

            if ($cashflow->value != $sale->getTotalCost()) {
                echo "{$sale->id}" . PHP_EOL;
            }

            if ($cashflow->save()) {
                $saved++;

                $sale->link('cashflow', $cashflow);
                foreach ($cashflowProducts as $cashflowProduct) {
                    $cashflowProduct->cashflow_id = $cashflow->id;
                    $cashflowProduct->save(false);
                }

                $cashflowPayment = new \core\models\finance\CompanyCashflowPayment([
                    'cashflow_id' => $cashflow->id,
                    'payment_id'  => $sale->payment_id ?: \core\helpers\company\PaymentHelper::CASH_ID,
                    'value'       => $cashflow->value
                ]);
                $cashflowPayment->save(false);
            }
        }

        echo "Saved: {$saved}";
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $oldSales = Sale::find()
            ->distinct()
            ->joinWith('saleProducts')
            ->innerJoin('{{%sale_product_cashflow}}',
                '{{%warehouse_sale_product}}.id = {{%sale_product_cashflow}}.sale_product_id');

        echo "Total: {$oldSales->count()}" . PHP_EOL;

        $saved = 0;
        foreach ($oldSales->each(100) as $sale) {
            /** @var Sale $sale */

            $sale->cashflow->unlinkAll('products');
            $sale->cashflow->unlinkAll('payments');
            $sale->cashflow->delete();

            foreach ($sale->saleProducts as $pInd => $saleProduct) {
                $saleProductCashflow = \core\models\finance\CompanyCashflow::find()->innerJoin('{{%sale_product_cashflow}}',
                    '{{%sale_product_cashflow}}.cashflow_id = {{%company_cashflows}}.id')
                    ->andWhere(['sale_product_id' => $saleProduct->id])->one();
                if ($saleProductCashflow) {
                    $saleProductCashflow->updateAttributes([
                        'status'     => \core\models\finance\CompanyCashflow::STATUS_ACTIVE,
                        'is_deleted' => false
                    ]);
                }
            }
        }

        echo "Deleted: {$saved}";
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180627_064408_parse_old_sales cannot be reverted.\n";

        return false;
    }
    */
}
