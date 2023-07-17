<?php

use yii\db\Migration;

/**
 * Class m180109_095929_alter_company_cashflow_updated_by
 */
class m180109_095929_alter_company_cashflow_updated_by extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->execute("ALTER TABLE {{%company_cashflows}} ALTER COLUMN updated_by DROP DEFAULT");
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->execute("ALTER TABLE {{%company_cashflows}} ALTER COLUMN updated_by SET DEFAULT 8");
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180109_095929_alter_company_cashflow_updated_by cannot be reverted.\n";

        return false;
    }
    */
}
