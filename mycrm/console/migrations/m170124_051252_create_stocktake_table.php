<?php

use yii\db\Migration;

/**
 * Handles the creation of table `stocktake`.
 */
class m170124_051252_create_stocktake_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%warehouse_stocktake}}', [
            'id' => $this->primaryKey(),
            'company_id' => $this->integer()->unsigned()->notNull(),
            'creator_id' => $this->integer()->unsigned()->notNull(),
            'category_id' => $this->integer()->unsigned(),
            'name' => $this->string()->notNull(),
            'description' => $this->string(),
            'status' => $this->integer()->defaultValue(1),
            'type_of_products' => $this->integer()->unsigned(),
            'created_at' => $this->dateTime(),
            'updated_at' => $this->dateTime(),
        ]);

        $this->addForeignKey('fk_stocktake_company', '{{%warehouse_stocktake}}', 'company_id', '{{%companies}}', 'id');
        $this->addForeignKey('fk_stocktake_creator', '{{%warehouse_stocktake}}', 'creator_id', '{{%users}}', 'id');
        $this->addForeignKey('fk_stocktake_category', '{{%warehouse_stocktake}}', 'category_id', '{{%warehouse_category}}', 'id');

        $this->createIndex('warehouse_stocktake_company_id_idx', '{{%warehouse_stocktake}}', 'company_id');
        $this->createIndex('warehouse_stocktake_status_idx', '{{%warehouse_stocktake}}', 'status');

        $this->createTable('{{%warehouse_stocktake_product}}', [
            'id' => $this->primaryKey(),
            'product_id' => $this->integer()->unsigned()->notNull(),
            'purchase_price' => $this->double()->notNull(),
            'recorded_stock_level' => $this->double()->notNull(),
            'actual_stock_level' => $this->double()->notNull(),
            'stocktake_id' => $this->integer()->unsigned()->notNull(),
            'apply_changes' => $this->boolean()->defaultValue(false)->notNull()
        ]);

        $this->addForeignKey('fk_stocktake_product_product', '{{%warehouse_stocktake_product}}', 'product_id',
            '{{%warehouse_product}}', 'id');
        $this->addForeignKey('fk_product_stocktake', '{{%warehouse_stocktake_product}}', 'stocktake_id',
            '{{%warehouse_stocktake}}', 'id');
    }

    public function safeDown()
    {
        $this->dropTable('{{%warehouse_stocktake_product}}');
        $this->dropTable('{{%warehouse_stocktake}}');
    }
}
