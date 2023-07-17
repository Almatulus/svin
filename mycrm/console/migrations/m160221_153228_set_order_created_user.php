<?php

use yii\db\Migration;
use yii\db\Schema;

class m160221_153228_set_order_created_user extends Migration
{
    public function up()
    {
        $this->addColumn("crm_orders", "created_user_id", Schema::TYPE_INTEGER . " NULL");
        $this->addForeignKey("fk_orders_created_user", "crm_orders", "created_user_id", "crm_users", "id");
    }

    public function down()
    {
        $this->dropForeignKey("fk_orders_created_user", "crm_orders");
        $this->dropColumn("crm_orders", "created_user_id");
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
