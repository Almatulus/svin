<?php

use yii\db\Migration;

class m170106_091243_add_company_tariff extends Migration
{
    /*public function up()
    {

    }

    public function down()
    {
        echo "m170106_091243_add_company_tariff cannot be reverted.\n";

        return false;
    }*/

    
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
        $this->addColumn('{{%companies}}', 'tariff_id', $this->integer());
    }

    public function safeDown()
    {
        $this->dropColumn('{{%companies}}', 'tariff_id');
    }
    
}
