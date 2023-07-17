<?php

use yii\db\Migration;

class m161123_054600_warehouse_init extends Migration
{
    // public function up()
    // {

    // }

    // public function down()
    // {
    //     echo "m161123_054600_warehouse_init cannot be reverted.\n";

    //     return false;
    // }

    
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
        // Datatable structure
        $this->createTable('{{%warehouse_manufacturer}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(),
            'company_id' => $this->integer()
        ]);
        $this->addForeignKey('fk_manufacturer_company', '{{%warehouse_manufacturer}}', 'company_id', '{{%companies}}', 'id');

        $this->createTable('{{%warehouse_product_unit}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(),
        ]);

        $this->createTable('{{%warehouse_category}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(),
            'parent_id' => $this->integer(),
            'company_id' => $this->integer()
        ]);

        $this->addForeignKey('fk_category_parent', '{{%warehouse_category}}', 'parent_id', '{{%warehouse_category}}', 'id');
        $this->addForeignKey('fk_category_company', '{{%warehouse_category}}', 'company_id', '{{%companies}}', 'id');

        $this->createTable('{{%warehouse_product}}', [
            'id' => $this->primaryKey(),
            'barcode' => $this->string(),
            'description' => $this->text(),
            'min_quantity' => $this->double(),
            'quantity' => $this->double(),
            'name' => $this->string()->notNull(),
            'price' => $this->double(),
            'sku' => $this->string()->comment('Артикул'),
            'type' => $this->integer()->notNull(),
            'vat' => $this->double()->comment('НДС'),
            'category_id' => $this->integer(),
            'unit_id' => $this->integer(),
            'manufacturer_id' => $this->integer()
        ]);

        $this->createIndex('name', '{{%warehouse_product}}', 'name');
        $this->addForeignKey('fk_product_category', '{{%warehouse_product}}', 'category_id', '{{%warehouse_category}}', 'id');
        $this->addForeignKey('fk_product_unit', '{{%warehouse_product}}', 'unit_id', '{{%warehouse_product_unit}}', 'id');
        $this->addForeignKey('fk_product_manufacturer', '{{%warehouse_product}}', 'manufacturer_id', '{{%warehouse_manufacturer}}', 'id');

        // Pruduct units
        $this->insert("{{%warehouse_product_unit}}", [
            'name' => 'упаковка',
        ]);
        $this->insert("{{%warehouse_product_unit}}", [
            'name' => 'грамм',
        ]);
        $this->insert("{{%warehouse_product_unit}}", [
            'name' => 'миллилитр',
        ]);
        $this->insert("{{%warehouse_product_unit}}", [
            'name' => 'штука',
        ]);

        // Rbac
        $auth = Yii::$app->authManager;

        // Division permissions
        $warehouseCreate = $auth->createPermission('warehouseCreate');
        $auth->add($warehouseCreate);
        $warehouseUpdate = $auth->createPermission('warehouseUpdate');
        $auth->add($warehouseUpdate);
        $warehouseView = $auth->createPermission('warehouseView');
        $auth->add($warehouseView);
        $warehouseDelete = $auth->createPermission('warehouseDelete');
        $auth->add($warehouseDelete);

        $warehouseAdmin = $auth->createPermission('warehouseAdmin');
        $auth->add($warehouseAdmin);
        $auth->addChild($warehouseAdmin, $warehouseView);
        $auth->addChild($warehouseAdmin, $warehouseUpdate);
        $auth->addChild($warehouseAdmin, $warehouseCreate);
        $auth->addChild($warehouseAdmin, $warehouseDelete);

        $administrator = Yii::$app->authManager->getRole('administrator');
        $auth->addChild($administrator, $warehouseAdmin);
    }

    public function safeDown()
    {
        $this->dropTable('{{%warehouse_product}}');
        $this->dropTable('{{%warehouse_product_unit}}');
        $this->dropTable('{{%warehouse_category}}');
        $this->dropTable('{{%warehouse_manufacturer}}');
    }
    
}
