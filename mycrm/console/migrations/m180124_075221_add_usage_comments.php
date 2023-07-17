<?php

use yii\db\Migration;

/**
 * Class m180124_075221_add_usage_comments
 */
class m180124_075221_add_usage_comments extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('{{%warehouse_usage}}', 'comments', $this->string());
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn('{{%warehouse_usage}}', 'comments');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180124_075221_add_usage_comments cannot be reverted.\n";

        return false;
    }
    */
}
