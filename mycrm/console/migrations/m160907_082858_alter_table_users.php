<?php

use core\models\user\User;
use yii\db\Migration;

class m160907_082858_alter_table_users extends Migration
{
    public function up()
    {
        $this->addColumn(User::tableName(), 'forgot_hash', $this->string());
    }

    public function down()
    {
        $this->dropColumn(User::tableName(), 'forgot_hash');
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
