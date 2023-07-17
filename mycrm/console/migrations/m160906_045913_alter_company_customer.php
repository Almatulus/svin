<?php

use core\models\customer\CompanyCustomer;
use yii\db\Migration;

class m160906_045913_alter_company_customer extends Migration
{
    public function up()
    {
        $this->addColumn(CompanyCustomer::tableName(), 'discount_granted_by', $this->smallInteger()
                                                                                   ->comment('Скидка получена: 0=>"Программа лояльности",1=>Категория')->defaultValue(0));
    }

    public function down()
    {
        $this->dropColumn('CompanyCustomer', 'discount_granted_by');
    }

    /*
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
    }

    public function safeDown()
    {
    }
    */
}
