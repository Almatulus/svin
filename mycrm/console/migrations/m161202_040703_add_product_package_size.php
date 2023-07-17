<?php

use yii\db\Migration;

class m161202_040703_add_product_package_size extends Migration
{
    // public function up()
    // {

    // }

    // public function down()
    // {
    //     echo "m161202_040703_add_product_package_size cannot be reverted.\n";

    //     return false;
    // }

    
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
        $this->addColumn("{{%warehouse_product}}", 'package_size', $this->double());
        $this->addColumn("{{%warehouse_product}}", 'stock_unit_id', $this->integer());

        $this->addForeignKey('fk_product_stock_unit', '{{%warehouse_product}}', 'stock_unit_id', '{{%warehouse_product_unit}}', 'id');

        \core\models\warehouse\Product::updateAll(['stock_unit_id' => 1]);
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_product_stock_unit', '{{%warehouse_product}}');
        $this->dropColumn("{{%warehouse_product}}", 'stock_unit_id');
        $this->dropColumn("{{%warehouse_product}}", 'package_size');
    }
    
}
