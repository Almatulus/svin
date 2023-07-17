<?php

use yii\db\Migration;

class m170612_093229_add_license_number_to_company extends Migration
{
    public function up()
    {
        $this->addColumn("{{%companies}}", 'license_number', $this->string());
    }

    public function down()
    {
        $this->dropColumn("{{%companies}}", 'license_number');
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
