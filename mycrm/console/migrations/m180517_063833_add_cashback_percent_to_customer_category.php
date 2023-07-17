<?php

use yii\db\Migration;

/**
 * Class m180517_063833_add_cashback_percent_to_customer_category
 */
class m180517_063833_add_cashback_percent_to_customer_category extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%customer_categories}}', 'cashback_percent', $this->smallInteger());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%customer_categories}}', 'cashback_percent');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180517_063833_add_cashback_percent_to_customer_category cannot be reverted.\n";

        return false;
    }
    */
}
