<?php

use core\models\company\Company;
use yii\db\Migration;

/**
 * Class m180402_062152_drop_company_calculation_method
 */
class m180402_062152_drop_company_calculation_method extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->dropColumn('{{%companies}}', 'calculation_method');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->addColumn('{{%companies}}', 'calculation_method',
            $this->integer()->notNull()->defaultValue(Company::CALCULATE_STRAIGHT));
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180402_062152_drop_company_calculation_method cannot be reverted.\n";

        return false;
    }
    */
}
