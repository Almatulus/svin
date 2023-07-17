<?php

use core\models\division\Division;
use core\models\finance\CompanyCashflow;
use core\models\finance\CompanyContractor;
use core\models\finance\CompanyCostItem;
use core\models\warehouse\Delivery;
use core\models\warehouse\DeliveryProduct;
use yii\db\Migration;
use yii\db\Query;
use yii\helpers\ArrayHelper;

class m170728_034018_remove_mgm_data extends Migration
{
    public function safeUp()
    {
        $cost_item_ids = ArrayHelper::getColumn(CompanyCostItem::findAll(['is_root' => 0, 'company_id' => 131]), 'id');
        CompanyCashflow::deleteAll(['cost_item_id' => $cost_item_ids]);
        (new Query)
            ->createCommand()
            ->delete('{{%division_cost_items}}', [
                'cost_item_id' => $cost_item_ids
            ])
            ->execute();
        CompanyCostItem::deleteAll(['id' => $cost_item_ids]);

        $division_ids = ArrayHelper::getColumn(Division::findAll(['company_id' => 131]), 'id');
        $contractor_ids = ArrayHelper::getColumn(CompanyContractor::findAll(['division_id' => $division_ids]), 'id');
        $delivery_ids = ArrayHelper::getColumn(Delivery::findAll(['contractor_id' => $contractor_ids]), 'id');
        CompanyCashflow::deleteAll(['contractor_id' => $contractor_ids]);
        DeliveryProduct::deleteAll(['delivery_id' => $delivery_ids]);
        Delivery::deleteAll(['contractor_id' => $contractor_ids]);
        CompanyContractor::deleteAll(['id' => $contractor_ids]);
    }

    public function safeDown()
    {

    }
}
