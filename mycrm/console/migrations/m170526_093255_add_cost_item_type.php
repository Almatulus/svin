<?php

use core\models\finance\CompanyCostItem;
use yii\db\Migration;

class m170526_093255_add_cost_item_type extends Migration
{

    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
        $this->addColumn("{{%company_cost_items}}", "cost_item_type", $this->integer());

        $costItems = CompanyCostItem::find();

        foreach($costItems->each(20) as $costItem) {
            $type = null;
            if ($costItem->is_salary == true) {
                $type = CompanyCostItem::COST_ITEM_TYPE_SALARY;
            } else if ($costItem->is_order == true) {
                $type = CompanyCostItem::COST_ITEM_TYPE_SERVICE;
            } else if ($costItem->name == Yii::t('app', 'COST ITEM INCOME PRODUCT')) {
                $type = CompanyCostItem::COST_ITEM_TYPE_PRODUCT_SALE;
            }

            if ($type) {
                echo "{$costItem->name} : {$type}\n";
                $costItem->updateAttributes(['cost_item_type' => $type]);
            }

        }

    }

    public function safeDown()
    {
        $this->dropColumn("{{%company_cost_items}}", "cost_item_type");
    }
}
