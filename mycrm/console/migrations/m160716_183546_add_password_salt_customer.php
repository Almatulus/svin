<?php

use core\models\customer\Customer;
use yii\db\Migration;

class m160716_183546_add_password_salt_customer extends Migration
{
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
        $this->addColumn(Customer::tableName(), "password_hash", $this->string());
        $this->addColumn(Customer::tableName(), "salt", $this->string());
    }

    public function safeDown()
    {
        $this->dropColumn(Customer::tableName(), "password_hash");
        $this->dropColumn(Customer::tableName(), "salt");
    }
}
