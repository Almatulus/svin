<?php

use core\models\finance\CompanyCostItem;
use yii\db\Migration;

class m170524_101754_create_division_cost_items extends Migration
{
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
        $this->createTable("{{%division_cost_items}}", [
            'division_id' => $this->integer()->notNull(),
            'cost_item_id' => $this->integer()->notNull()
        ]);

        $this->addPrimaryKey('division_cost_items_pk', '{{%division_cost_items}}', ['division_id', 'cost_item_id']);

        $this->addForeignKey('fk_division_cost_items_divsion', '{{%division_cost_items}}', 'division_id', '{{%divisions}}', 'id');
        $this->addForeignKey('fk_division_cost_items_cost_item', '{{%division_cost_items}}', 'cost_item_id', '{{%company_cost_items}}', 'id');

        $this->createIndex('division_cost_items_division_id', '{{%division_cost_items}}', 'division_id');
        $this->createIndex('division_cost_items_cost_item_id', '{{%division_cost_items}}', 'cost_item_id');
        $this->createIndex('uq_division_cost_items', '{{%division_cost_items}}', ['division_id', 'cost_item_id'], true);

        $this->setDivision();
    }

    public function safeDown()
    {
        $this->dropTable("{{%division_cost_items}}");
    }

    private function setDivision()
    {
        /* @var CompanyCostItem[] $costItems */
        $costItems = CompanyCostItem::find()
            ->deletable()
            ->all();

        foreach ($costItems as $key => $costItem) {
            $division_id = $costItem->company->getDivisions()->select('id')->orderBy('id ASC')->scalar();

            echo "{$costItem->id} : {$costItem->name} : {$costItem->company_id} : {$division_id}\n";

            if ($division_id) {
                $this->insert('{{%division_cost_items}}', [
                    'cost_item_id' => $costItem->id,
                    'division_id' => $division_id,
                ]);
            }
        }

    }
}
