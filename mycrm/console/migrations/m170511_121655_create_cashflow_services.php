<?php

use core\models\finance\CompanyCashflowService;
use yii\db\Migration;

class m170511_121655_create_cashflow_services extends Migration
{
    public function safeUp()
    {
        $this->createServicesTable();
        $this->createProductsTable();
        $this->fixCashflowOrderItems();
        $this->moveOrderItemsToCahsflow();
    }

    public function safeDown()
    {
        $this->createOrderItemsTable();
        $this->revertOrderItemsToCahsflowServices();
    }

    private function fixCashflowOrderItems()
    {
        $sql = <<<SQL
            SELECT order_id, type, model_id, count(*) as count 
            FROM {{%company_cashflow_order_items}} 
            GROUP BY order_id, type, model_id 
            HAVING count(*) > 1
SQL;
        $cashFlows = Yii::$app->db->createCommand($sql)->queryAll();

        foreach ($cashFlows as $cashFlow) {
            $sql = <<<SQL
                SELECT * 
                FROM {{%company_cashflow_order_items}} 
                WHERE 
                order_id = :order_id AND  
                type = :type AND
                model_id = :model_id
SQL;
            $orderItems = Yii::$app->db->createCommand($sql, [
                ':order_id' => $cashFlow['order_id'],
                ':type' => $cashFlow['type'],
                ':model_id' => $cashFlow['model_id'],
            ])->queryAll();

            $i = 0;
            foreach ($orderItems as $orderItem) {
                if ($i++ == 0) {
                    continue;
                }
                $this->delete('{{%company_cashflow_order_items}}', 'id = :id', [':id' => $orderItem['id']]);
            }
        }
    }

    private function createServicesTable()
    {
        $this->createTable('{{%company_cashflow_services}}', [
            'id' => $this->primaryKey(),
            'cashflow_id' => $this->integer()->notNull(),
            'order_service_id' => $this->integer()->notNull(),
        ]);

        $this->addForeignKey('fk_company_cashflow_services_cashflow',
            '{{%company_cashflow_services}}', 'cashflow_id',
            '{{%company_cashflows}}', 'id');
        $this->addForeignKey('fk_company_cashflow_services_order_service',
            '{{%company_cashflow_services}}', 'order_service_id',
            '{{%order_services}}', 'id');
        $this->createIndex('uq_company_cashflow_services_cashflow',
            '{{%company_cashflow_services}}', ['cashflow_id'], true);
        $this->createIndex('uq_company_cashflow_services_order_service',
            '{{%company_cashflow_services}}', ['order_service_id'], true);
    }

    private function createProductsTable()
    {
        $this->createTable('{{%company_cashflow_products}}', [
            'id' => $this->primaryKey(),
            'cashflow_id' => $this->integer()->notNull(),
            'order_service_product_id' => $this->integer()->notNull(),
        ]);

        $this->addForeignKey('fk_company_cashflow_services_cashflow',
            '{{%company_cashflow_products}}', 'cashflow_id',
            '{{%company_cashflows}}', 'id');
        $this->addForeignKey('fk_company_cashflow_products_order_service_product',
            '{{%company_cashflow_products}}', 'order_service_product_id',
            '{{%order_service_products}}', 'id');
        $this->createIndex('uq_company_cashflow_products_cashflow',
            '{{%company_cashflow_products}}', ['cashflow_id'], true);
        $this->createIndex('uq_company_cashflow_products_order_product',
            '{{%company_cashflow_products}}', ['order_service_product_id'], true);
    }

    private function createOrderItemsTable()
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
    }

    private function moveOrderItemsToCahsflow()
    {
        $this->moveServices();
        $this->moveProducts();
        $this->dropTable('{{%company_cashflow_order_items}}');
    }

    private function revertOrderItemsToCahsflowServices()
    {
        $this->revertServices();
        $this->revertProducts();
        $this->dropTable('{{%company_cashflow_services}}');
        $this->dropTable('{{%company_cashflow_products}}');
    }

    private function moveServices()
    {
        $sql = 'SELECT * FROM {{%company_cashflow_order_items}} WHERE type = 1';
        $orderItems = Yii::$app->db->createCommand($sql)->queryAll();

        foreach ($orderItems as $orderItem) {
            $this->insert('{{%company_cashflow_services}}', [
                'cashflow_id' => $orderItem['cashflow_id'],
                'order_service_id' => $orderItem['model_id'],
            ]);
        }

        $cashflowServices = Yii::$app->db->createCommand('SELECT * FROM {{%company_cashflow_services}}')->queryAll();
        if (count($orderItems) !== count($cashflowServices)) {
            throw new DomainException('Error moving order items to cashflow services');
        }
    }

    private function moveProducts()
    {
        $sql = 'SELECT * FROM {{%company_cashflow_order_items}} WHERE type = 2';
        $orderItems = Yii::$app->db->createCommand($sql)->queryAll();

        foreach ($orderItems as $orderItem) {
            $this->insert('{{%company_cashflow_products}}', [
                'cashflow_id' => $orderItem['cashflow_id'],
                'order_service_product_id' => $orderItem['model_id'],
            ]);
        }

        $cashflowProducts = Yii::$app->db->createCommand('SELECT * FROM {{%company_cashflow_products}}')->queryAll();
        if (count($orderItems) !== count($cashflowProducts)) {
            throw new DomainException('Error moving order items to cashflow products');
        }
    }

    private function revertServices()
    {
        $cashflowServices = Yii::$app->db->createCommand('SELECT * FROM {{%company_cashflow_services}}')->queryAll();

        $data = [];
        foreach ($cashflowServices as $cashflowService) {
            $orderService = Yii::$app->db
                ->createCommand('SELECT * FROM {{%order_services}} WHERE id = :order_service_id', [
                    ':order_service_id' => $cashflowService['order_service_id']
                ])
                ->queryOne();
            $data[] = [
                'cashflow_id' => $cashflowService['cashflow_id'],
                'order_id' => $orderService['order_id'],
                'type' => 1,
                'model_id' => $cashflowService['order_service_id'],
            ];
        }
        $this->batchInsert('{{%company_cashflow_order_items}}', ['cashflow_id', 'order_id', 'type', 'model_id'], $data);
    }

    private function revertProducts()
    {
        $cashflowProducts = Yii::$app->db->createCommand('SELECT * FROM {{%company_cashflow_products}}')->queryAll();

        $data = [];
        foreach ($cashflowProducts as $cashflowService) {
            $orderProduct = Yii::$app->db
                ->createCommand('SELECT * FROM {{%order_service_products}} WHERE id = :order_service_product_id', [
                    ':order_service_product_id' => $cashflowService['order_service_product_id']
                ])
                ->queryOne();
            $orderService = Yii::$app->db
                ->createCommand('SELECT * FROM {{%order_services}} WHERE id = :order_service_id', [
                    ':order_service_id' => $orderProduct['order_service_id']
                ])
                ->queryOne();

            $data[] = [
                'cashflow_id' => $cashflowService['cashflow_id'],
                'order_id' => $orderService['order_id'],
                'type' => 2,
                'model_id' => $cashflowService['order_service_product_id'],
            ];
        }
        $this->batchInsert('{{%company_cashflow_order_items}}', ['cashflow_id', 'order_id', 'type', 'model_id'], $data);
    }
}
