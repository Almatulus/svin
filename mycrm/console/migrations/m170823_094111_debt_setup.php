<?php

use core\models\company\Company;
use core\helpers\finance\CompanyCostItemHelper;
use core\models\finance\CompanyCostItem;
use yii\db\Migration;

class m170823_094111_debt_setup extends Migration
{
    public function safeUp()
    {
        $this->dropColumn('{{%company_cost_items}}', 'is_order');
        $this->dropColumn('{{%company_cost_items}}', 'is_salary');

        $this->moveColumn();
        $this->addCostItems();
        $this->renameCostItems();
    }

    public function safeDown()
    {
        $this->addColumn(
            '{{%company_cost_items}}',
            "is_order",
            $this->integer()->defaultValue(0)
        );
        $this->addColumn(
            '{{%company_cost_items}}',
            "is_salary",
            $this->integer()->defaultValue(0)
        );

        $this->revertMoveColumn();
        $this->revertAddCostItems();
        $this->revertRenameCostItems();
    }

    private function moveColumn()
    {
        $this->addColumn(
            '{{%company_cost_items}}',
            'is_deletable',
            $this->boolean()->notNull()->defaultValue(true)
        );
        $this->update(
            '{{%company_cost_items}}',
            ['is_deletable' => false],
            ['is_root' => 1]
        );

        $this->dropColumn('{{%company_cost_items}}', 'is_root');
    }

    private function addCostItems()
    {
        $cost_items = [];
        foreach (Company::find()->each() as $model) {
            $cost_items[] = [
                'name'           => 'COST ITEM INCOME DEBT PAYMENT',
                'type'           => CompanyCostItem::TYPE_INCOME,
                'comments'       => null,
                'company_id'     => $model->id,
                'cost_item_type' => CompanyCostItem::COST_ITEM_TYPE_DEBT_PAYMENT,
                'is_deletable'   => false
            ];
        }
        $this->batchInsert('{{%company_cost_items}}', [
            'name',
            'type',
            'comments',
            'company_id',
            'cost_item_type',
            'is_deletable',
        ], $cost_items);
    }

    private function renameCostItems()
    {
        $cost_item_templates = CompanyCostItemHelper::getInitialItems();

        foreach ($cost_item_templates as $template) {
            $this->update('{{%company_cost_items}}', [
                'name' => $template['name']
            ], [
                'name'         => Yii::t('app', $template['name']),
                'is_deletable' => false
            ]);
        }
    }

    private function revertRenameCostItems()
    {
        $cost_item_templates = CompanyCostItemHelper::getInitialItems();

        foreach ($cost_item_templates as $template) {
            $this->update('{{%company_cost_items}}', [
                'name' => Yii::t('app', $template['name'])
            ], [
                'name'         => $template['name'],
                'is_deletable' => false
            ]);
        }
    }

    private function revertMoveColumn()
    {
        $this->addColumn(
            '{{%company_cost_items}}',
            'is_root',
            $this->integer()->notNull()->defaultValue(0)
        );
        $this->update(
            '{{%company_cost_items}}',
            ['is_root' => 1],
            ['is_deletable' => false]
        );
        $this->dropColumn('{{%company_cost_items}}', 'is_deletable');
    }

    private function revertAddCostItems()
    {
        $this->delete('{{%company_cost_items}}', [
            'cost_item_type' => CompanyCostItem::COST_ITEM_TYPE_DEBT_PAYMENT,
        ]);
    }

}
