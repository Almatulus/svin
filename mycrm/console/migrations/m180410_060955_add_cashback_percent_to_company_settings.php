<?php

use yii\db\Migration;

/**
 * Class m180410_060955_add_cashback_percent_to_company_settings
 */
class m180410_060955_add_cashback_percent_to_company_settings extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%companies}}', 'cashback_percent', $this->integer());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%companies}}', 'cashback_percent');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180410_060955_add_cashback_percent_to_company_settings cannot be reverted.\n";

        return false;
    }
    */
}
