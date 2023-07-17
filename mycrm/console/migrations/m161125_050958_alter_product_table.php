<?php

use yii\db\Migration;

class m161125_050958_alter_product_table extends Migration
{
    // public function up()
    // {

    // }

    // public function down()
    // {
    //     echo "m161125_050958_alter_product_table cannot be reverted.\n";

    //     return false;
    // }

    
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
        $this->dropColumn('{{%warehouse_product}}', 'type');

        $this->createTable('{{%warehouse_product_type}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(),
        ]);

        $this->createTable('{{%warehouse_product_type_map}}', [
            'id' => $this->primaryKey(),
            'product_id' => $this->integer()->notNull(),
            'type_id' => $this->integer()->notNull(),
        ]);

        $this->addForeignKey('fk_product', '{{%warehouse_product_type_map}}', 'product_id', '{{%warehouse_product}}', 'id');
        $this->addForeignKey('fk_product_type', '{{%warehouse_product_type_map}}', 'type_id', '{{%warehouse_product_type}}', 'id');

        $this->insert('{{%warehouse_product_type}}', [
            'name' => 'Для продажи'
        ]);
        $this->insert('{{%warehouse_product_type}}', [
            'name' => 'Для списания'
        ]);
    }

    public function safeDown()
    {
        $this->addColumn('{{%warehouse_product}}', 'type', $this->integer());

        $this->dropTable('{{%warehouse_product_type_map}}');
        $this->dropTable('{{%warehouse_product_type}}');
    }
    
}
