<?php

use yii\db\Migration;

class m160221_162015_remove_order_datetime_column extends Migration
{
    public function up()
    {
        $this->dropColumn("crm_orders", "datetime");
    }

    public function down()
    {
        $this->addColumn("crm_orders", "datetime", \yii\db\Schema::TYPE_DATETIME . " NOT NULL DEFAULT NOW()");
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
