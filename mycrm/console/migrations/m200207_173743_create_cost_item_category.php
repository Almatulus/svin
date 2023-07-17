<?php

use core\models\finance\CompanyCostItem;
use core\models\finance\CompanyCostItemCategory;
use yii\db\Migration;

/**
 * Class m200207_173743_create_cost_item_category
 */
class m200207_173743_create_cost_item_category extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%company_cost_item_categories}}', [
            'id'         => $this->primaryKey(),
            'name'       => $this->string()->unique()->notNull(),
            'company_id' => $this->integer()->notNull(),
        ]);

        $this->addForeignKey('fk_company_cost_item_categories_company',
            '{{%company_cost_item_categories}}', 'company_id',
            '{{%companies}}', 'id');

        $this->addColumn(
            CompanyCostItem::tableName(),
            'category_id',
            $this->integer()
        );

        $this->addForeignKey('fk_company_cost_item_category',
            CompanyCostItem::tableName(),
            'category_id',
            CompanyCostItemCategory::tableName(),
            'id'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn(CompanyCostItem::tableName(), 'category_id');
        $this->dropTable('{{%company_cost_item_categories}}');
    }
}
