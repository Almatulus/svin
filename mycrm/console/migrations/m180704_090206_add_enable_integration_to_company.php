<?php

use yii\db\Migration;

/**
 * Class m180704_090206_add_enable_integration_to_company
 */
class m180704_090206_add_enable_integration_to_company extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%companies}}', 'enable_integration', $this->boolean()->defaultValue(false));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%companies}}', 'enable_integration');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180704_090206_add_enable_integration_to_company cannot be reverted.\n";

        return false;
    }
    */
}
