<?php

use yii\db\Migration;

class m161202_064305_add_deliveries extends Migration
{
    // public function up()
    // {

    // }

    // public function down()
    // {
    //     echo "m161202_064305_add_deliveries cannot be reverted.\n";

    //     return false;
    // }

    
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
        $this->createTable('{{%warehouse_delivery}}', [
            'id' => $this->primaryKey(),
            'company_id' => $this->integer()->notNull(),
            'contractor_id' => $this->integer(),
            'delivery_date' => $this->date(),
            'invoice_number' => $this->string(),
            'notes' => $this->string(),
            'type' => $this->integer(),
            'created_at' => $this->dateTime(),
            'updated_at' => $this->dateTime()
        ]);

        $this->addForeignKey('fk_delivery_company', '{{%warehouse_delivery}}', 'company_id', '{{%companies}}', 'id');
        $this->addForeignKey('fk_delivery_contractor', '{{%warehouse_delivery}}', 'contractor_id', '{{%company_contractors}}', 'id');

        $this->createTable('{{%warehouse_delivery_product}}', [
            'id' => $this->primaryKey(),
            'quantity' => $this->double()->notNull(),
            'price' => $this->double()->notNull(),
            'product_id' => $this->integer()->notNull(),
            'delivery_id' => $this->integer()->notNull()
        ]);

        $this->addForeignKey('fk_product', '{{%warehouse_delivery_product}}', 'product_id', '{{%warehouse_product}}', 'id');
        $this->addForeignKey('fk_delivery', '{{%warehouse_delivery_product}}', 'delivery_id', '{{%warehouse_delivery}}', 'id');
    }

    public function safeDown()
    {
        $this->dropTable('{{%warehouse_delivery_product}}');
        $this->dropTable('{{%warehouse_delivery}}');
    }
    
}
