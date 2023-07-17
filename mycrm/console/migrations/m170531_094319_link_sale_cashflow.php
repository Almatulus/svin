<?php

use yii\db\Migration;

class m170531_094319_link_sale_cashflow extends Migration
{
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
        $this->createTable('{{%sale_product_cashflow}}', [
            'cashflow_id' => $this->integer()->notNull(),
            'sale_product_id' => $this->integer()->notNull()
        ]);

        $this->addPrimaryKey('sale_product_cashflow_pk', '{{%sale_product_cashflow}}', ['cashflow_id', 'sale_product_id']);

        $this->addForeignKey('fk_sale_product_cashflow_cashflow', '{{%sale_product_cashflow}}', 'cashflow_id', '{{%company_cashflows}}', 'id');
        $this->addForeignKey('fk_sale_product_cashflow_product', '{{%sale_product_cashflow}}', 'sale_product_id', '{{%warehouse_sale_product}}', 'id');

        $this->createIndex('sale_product_cashflow_cashflow_id', '{{%sale_product_cashflow}}', 'cashflow_id');
        $this->createIndex('sale_product_cashflow_sale_product_id', '{{%sale_product_cashflow}}', 'sale_product_id');
        $this->createIndex('uq_sale_product_cashflow', '{{%sale_product_cashflow}}', ['cashflow_id', 'sale_product_id'], true);
    }

    public function safeDown()
    {
        $this->dropTable('{{%sale_product_cashflow}}');
    }
}
