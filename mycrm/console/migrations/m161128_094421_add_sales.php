<?php

use yii\db\Migration;

class m161128_094421_add_sales extends Migration
{
    // public function up()
    // {

    // }

    // public function down()
    // {
    //     echo "m161128_094421_add_sales cannot be reverted.\n";

    //     return false;
    // }

    
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
        $this->createTable('{{%warehouse_sale}}', [
            'id' => $this->primaryKey(),
            'cash_id' => $this->integer(),
            'company_customer_id' => $this->integer(),
            'discount' => $this->integer(),
            'paid' => $this->double(),
            'staff_id' => $this->integer(),
            'sale_date' => $this->date(),
        ]);

        $this->addForeignKey('fk_sale_cash', '{{%warehouse_sale}}', 'cash_id', '{{%company_cashes}}', 'id');
        $this->addForeignKey('fk_sale_customer', '{{%warehouse_sale}}', 'company_customer_id', '{{%company_customers}}', 'id');
        $this->addForeignKey('fk_sale_staff', '{{%warehouse_sale}}', 'staff_id', '{{%staffs}}', 'id');

        $this->createTable('{{%warehouse_sale_product}}', [
            'id' => $this->primaryKey(),
            'quantity' => $this->double()->notNull(),
            'price' => $this->double(),
            'product_id' => $this->integer()->notNull(),
            'sale_id' => $this->integer()->notNull()
        ]);

        $this->addForeignKey('fk_product', '{{%warehouse_sale_product}}', 'product_id', '{{%warehouse_product}}', 'id');
        $this->addForeignKey('fk_sale', '{{%warehouse_sale_product}}', 'sale_id', '{{%warehouse_sale}}', 'id');
    }

    public function safeDown()
    {
        $this->dropTable('{{%warehouse_sale_product}}');
        $this->dropTable('{{%warehouse_sale}}');
    }
    
}
