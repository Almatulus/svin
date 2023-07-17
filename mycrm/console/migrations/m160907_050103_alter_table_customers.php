<?php

use core\models\customer\Customer;
use yii\db\Migration;

class m160907_050103_alter_table_customers extends Migration
{
    public function up()
    {
        $this->addColumn(Customer::tableName(), 'forgot_hash', $this->string());
    }

    public function down()
    {
        $this->dropColumn(Customer::tableName(), 'forgot_hash');
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
