<?php

use yii\db\Migration;

/**
 * Class m180125_044025_add_unlimited_messages_to_company
 */
class m180125_044025_add_unlimited_messages_to_company extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('{{%companies}}', 'unlimited_sms', $this->boolean()->defaultValue(false)->notNull());
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn('{{%companies}}', 'unlimited_sms');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180125_044025_add_unlimited_messages_to_company cannot be reverted.\n";

        return false;
    }
    */
}
