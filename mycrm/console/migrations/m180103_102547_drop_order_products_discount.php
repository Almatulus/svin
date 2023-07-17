<?php

use yii\db\Migration;

/**
 * Class m180103_102547_drop_order_products_discount
 */
class m180103_102547_drop_order_products_discount extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->dropColumn('{{%orders}}', 'products_discount');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->addColumn('{{%orders}}', 'products_discount', $this->integer()->defaultValue(0));
    }
}
