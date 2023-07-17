<?php

use yii\db\Migration;

class m160923_044526_add_image_order extends Migration
{
    public function up()
    {
        $this->addColumn('crm_images', 'order', $this->integer());
    }

    public function down()
    {
        $this->dropColumn('crm_images', 'order');
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
