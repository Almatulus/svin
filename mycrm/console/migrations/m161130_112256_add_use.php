<?php

use yii\db\Migration;

class m161130_112256_add_use extends Migration
{
    // public function up()
    // {

    // }

    // public function down()
    // {
    //     echo "m161130_112256_add_use cannot be reverted.\n";

    //     return false;
    // }

    
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {   
        $this->addColumn('{{%warehouse_product}}', 'company_id', $this->integer());
        $this->addForeignKey('fk_product_company', '{{%warehouse_product}}', 'company_id', '{{%companies}}', 'id');

        $this->addColumn('{{%warehouse_sale}}', 'company_id', $this->integer());
        $this->addForeignKey('fk_sale_company', '{{%warehouse_sale}}', 'company_id', '{{%companies}}', 'id');

        $this->createTable('{{%warehouse_usage}}', [
            'id' => $this->primaryKey(),
            'company_id' => $this->integer()->notNull(),
            'company_customer_id' => $this->integer(),
            'staff_id' => $this->integer(),
            'created_at' => $this->dateTime(),
            'updated_at' => $this->dateTime()
        ]);

        $this->addForeignKey('fk_usage_company', '{{%warehouse_usage}}', 'company_id', '{{%companies}}', 'id');
        $this->addForeignKey('fk_usage_customer', '{{%warehouse_usage}}', 'company_customer_id', '{{%company_customers}}', 'id');
        $this->addForeignKey('fk_usage_staff', '{{%warehouse_usage}}', 'staff_id', '{{%staffs}}', 'id');

        $this->createTable('{{%warehouse_usage_product}}', [
            'id' => $this->primaryKey(),
            'quantity' => $this->double()->notNull(),
            'product_id' => $this->integer()->notNull(),
            'usage_id' => $this->integer()->notNull()
        ]);

        $this->addForeignKey('fk_product', '{{%warehouse_usage_product}}', 'product_id', '{{%warehouse_product}}', 'id');
        $this->addForeignKey('fk_usage', '{{%warehouse_usage_product}}', 'usage_id', '{{%warehouse_usage}}', 'id');
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_product_company', '{{%warehouse_product}}');
        $this->dropForeignKey('fk_sale_company', '{{%warehouse_sale}}');

        $this->dropColumn('{{%warehouse_product}}', 'company_id');
        $this->dropColumn('{{%warehouse_sale}}', 'company_id');

        $this->dropTable('{{%warehouse_usage_product}}');
        $this->dropTable('{{%warehouse_usage}}');
    }
    
}
