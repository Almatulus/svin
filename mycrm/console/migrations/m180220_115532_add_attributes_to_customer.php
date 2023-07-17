<?php

use yii\db\Migration;

/**
 * Class m180220_115532_add_attributes_to_customer
 */
class m180220_115532_add_attributes_to_customer extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('{{%company_customers}}', 'insurance_policy_number', $this->string());
        $this->addColumn('{{%company_customers}}', 'insurance_expire_date', $this->date());
        $this->addColumn('{{%company_customers}}', 'insurer', $this->string());
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn('{{%company_customers}}', 'insurance_policy_number');
        $this->dropColumn('{{%company_customers}}', 'insurance_expire_date');
        $this->dropColumn('{{%company_customers}}', 'insurer');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180220_115532_add_attributes_to_customer cannot be reverted.\n";

        return false;
    }
    */
}
