<?php

use core\helpers\finance\CompanyCostItemHelper;
use core\models\finance\CompanyCostItem;
use yii\db\Migration;

class m170924_124006_fix_cost_item extends Migration
{
    public function safeUp()
    {
        $static_cost_items = array_map(
            function ($item) {
                return $item['name'];
            },
            CompanyCostItemHelper::getInitialItems()
        );

        CompanyCostItem::updateAll(
            ['is_deletable' => false],
            ['name' => $static_cost_items]
        );
    }

    public function safeDown()
    {

    }
}
