<?php

use yii\db\Migration;

/**
 * Class m180514_095706_add_delivery_is_deleted
 */
class m180514_095706_add_delivery_is_deleted extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%warehouse_delivery}}', 'is_deleted', $this->boolean()->defaultValue(false));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%warehouse_delivery}}', 'is_deleted');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180514_095706_add_delivery_is_deleted cannot be reverted.\n";

        return false;
    }
    */
}
