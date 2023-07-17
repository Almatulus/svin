<?php

use yii\db\Migration;

/**
 * Class m180125_071029_add_discount_to_sale_product
 */
class m180125_071029_add_discount_to_sale_product extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('{{%warehouse_sale_product}}', 'discount', $this->integer()->defaultValue(0)->notNull());
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn('{{%warehouse_sale_product}}', 'discount');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180125_071029_add_discount_to_sale_product cannot be reverted.\n";

        return false;
    }
    */
}
