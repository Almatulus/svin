<?php

use yii\db\Migration;
use core\models\finance\CompanyCash;

class m160904_054849_alter_company_cash extends Migration
{
    public function up()
    {
        $this->addColumn(CompanyCash::tableName(), "is_deletable", $this->boolean()->defaultValue(true));
    }

    public function down()
    {
        $this->dropColumn(CompanyCash::tableName(), "is_deletable");
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
