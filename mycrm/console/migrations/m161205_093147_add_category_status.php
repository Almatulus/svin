<?php

use yii\db\Migration;

class m161205_093147_add_category_status extends Migration
{
    public function up()
    {
        $this->addColumn('{{%service_categories}}', 'status', $this->integer()->defaultValue(1));
    }

    public function down()
    {
        $this->dropColumn('{{%service_categories}}', 'status');
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
