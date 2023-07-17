<?php

use yii\db\Migration;

class m170306_051312_add_cashflow_relations extends Migration
{
    /*public function up()
    {

    }

    public function down()
    {
        echo "m170306_051312_add_cashflow_relations cannot be reverted.\n";

        return false;
    }*/

    
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {   
        $this->createTable("{{%company_cashflow_order_items}}", [
            'id' => $this->primaryKey(),
            'cashflow_id' => $this->integer()->unsigned()->notNull(),
            'order_id' => $this->integer()->unsigned()->notNull(),
            'type' => $this->integer()->unsigned()->notNull(),
            'model_id' => $this->integer()->unsigned()->notNull()
        ]);

        $this->addForeignKey("fk_cashflow_order_item_cashflow", "{{%company_cashflow_order_items}}", "cashflow_id",
            "{{%company_cashflows}}", "id");
        $this->addForeignKey("fk_cashflow_order_item_order", "{{%company_cashflow_order_items}}", "order_id",
            "{{%orders}}", "id");
        $this->createIndex('company_cashflow_order_items_cashflow_id_idx', '{{%company_cashflow_order_items}}', 'cashflow_id');
        $this->createIndex('company_cashflow_order_items_order_id_idx', '{{%company_cashflow_order_items}}', 'order_id');

        // $this->createTable("{{%company_cashflow_order_services}}", [
        //     'id' => $this->primaryKey(),
        //     'cashflow_id' => $this->integer()->unsigned()->notNull(),
        //     'order_service_id' => $this->integer()->unsigned()->notNull()
        // ]);

        // $this->createTable("{{%company_cashflow_order_products}}", [
        //     'id' => $this->primaryKey(),
        //     'cashflow_id' => $this->integer()->unsigned()->notNull(),
        //     'order_product_id' => $this->integer()->unsigned()->notNull()
        // ]);

        // $this->addForeignKey("fk_cashflow_service_cashflow", "{{%company_cashflow_order_services}}", "cashflow_id",
        //     "{{%company_cashflows}}", "id");
        // $this->addForeignKey("fk_cashflow_service_order_service", "{{%company_cashflow_order_services}}", "order_service_id",
        //     "{{%order_services}}", "id");
        // $this->createIndex('company_cashflow_order_services_cashflow_id_idx', '{{%company_cashflow_order_services}}', 'cashflow_id');
        // $this->createIndex('company_cashflow_order_services_service_id_idx', '{{%company_cashflow_order_services}}', 'order_service_id');

        // $this->addForeignKey("fk_cashflow_product_cashflow", "{{%company_cashflow_order_products}}", "cashflow_id",
        //     "{{%company_cashflows}}", "id");
        // $this->addForeignKey("fk_cashflow_product_order_product", "{{%company_cashflow_order_products}}", "order_product_id",
        //     "{{%order_service_products}}", "id");
        // $this->createIndex('company_cashflow_order_products_cashflow_id_idx', '{{%company_cashflow_order_products}}', 'cashflow_id');
        // $this->createIndex('company_cashflow_order_products_product_id_idx', '{{%company_cashflow_order_products}}', 'order_product_id');
    }

    public function safeDown()
    {
        $this->dropTable('{{%company_cashflow_order_items}}');
//        $this->dropTable('{{%company_cashflow_order_products}}');
//        $this->dropTable('{{%company_cashflow_order_services}}');
    }
    
}
