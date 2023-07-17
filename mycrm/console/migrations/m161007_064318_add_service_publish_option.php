<?php

use yii\db\Migration;

class m161007_064318_add_service_publish_option extends Migration
{
    public function up()
    {
        $this->addColumn('crm_division_services', 'publish', $this->boolean()->defaultValue(true));
    }

    public function down()
    {
        $this->dropColumn('crm_division_services', 'publish');
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
