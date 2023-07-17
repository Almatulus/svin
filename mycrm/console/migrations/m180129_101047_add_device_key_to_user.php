<?php

use yii\db\Migration;

/**
 * Class m180129_101047_add_device_key_to_user
 */
class m180129_101047_add_device_key_to_user extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('{{%users}}', 'device_key', $this->string());
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn('{{%users}}', 'device_key');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180129_101047_add_device_key_to_user cannot be reverted.\n";

        return false;
    }
    */
}
