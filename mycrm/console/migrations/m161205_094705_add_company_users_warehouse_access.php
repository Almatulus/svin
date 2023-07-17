<?php

use yii\db\Migration;

class m161205_094705_add_company_users_warehouse_access extends Migration
{
    public function up()
    {
        $this->insert('crm_auth_item_child', [
            'child' => 'warehouseAdmin',
            'parent' => 'company'
        ]);
    }

    public function down()
    {
        $this->delete('crm_auth_item_child', [
            'child' => 'warehouseAdmin',
            'parent' => 'company'
        ]);
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
