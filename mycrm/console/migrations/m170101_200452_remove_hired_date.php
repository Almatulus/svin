<?php

use yii\db\Migration;

class m170101_200452_remove_hired_date extends Migration
{
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
        $this->dropColumn("{{%staffs}}", "hired_date");
    }

    public function safeDown()
    {
        $this->addColumn("{{%staffs}}", "hired_date", $this->dateTime());
    }
}
