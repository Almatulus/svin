<?php

use core\models\finance\CompanyCashflowService;
use yii\db\Migration;

class m170805_154448_fix_cashflow_services extends Migration
{
    public function safeUp()
    {
        /* @var CompanyCashflowService[] $companyCashflowServices */
        $companyCashflowServices = CompanyCashflowService::find()
            ->joinWith(['cashflow c', 'orderService os'])
            ->where("c.value != os.price * (100 - os.discount) * os.quantity / 100")
            ->orderBy('c.date')
            ->all();

        foreach ($companyCashflowServices as $cashflowService) {
            $companyCashflow = $cashflowService->cashflow;
            $finalePrice = $cashflowService->orderService->getFinalPrice();
            echo $companyCashflow->date . " ";
            echo $companyCashflow->value . " ";
            echo $finalePrice . "\n";
            $companyCashflow->value = $finalePrice;
            $companyCashflow->update(false);
        }
    }

    public function safeDown()
    {

    }
}
