<?php

use yii\db\Migration;

/**
 * Class m180212_083135_add_company_settings
 */
class m180212_083135_add_company_settings extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('{{%companies}}', 'notify_about_order', $this->boolean()->defaultValue(true)->notNull());
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn('{{%companies}}', 'notify_about_order');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180212_083135_add_company_settings cannot be reverted.\n";

        return false;
    }
    */
}
