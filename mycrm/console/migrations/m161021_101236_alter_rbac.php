<?php

use yii\db\Migration;

class m161021_101236_alter_rbac extends Migration
{
    public function up()
    {
        $time = time();

        $this->insert('crm_auth_item', [
            'name' => 'timetableView',
            'type' => 2,
            'created_at' => $time,
            'updated_at' => $time
        ]);

        $this->insert('crm_auth_item_child', [
            'child' => 'orderCreate',
            'parent' => 'timetableView'
        ]);

        $this->insert('crm_auth_item_child', [
            'child' => 'orderDelete',
            'parent' => 'timetableView'
        ]);

        $this->insert('crm_auth_item_child', [
            'child' => 'timetableView',
            'parent' => 'administrator'
        ]);

        $this->insert('crm_auth_item_child', [
            'child' => 'timetableView',
            'parent' => 'company'
        ]);
    }

    public function down()
    {
        $this->delete('crm_auth_item_child', [
            'child' => 'orderCreate',
            'parent' => 'timetableView'
        ]);

        $this->delete('crm_auth_item_child', [
            'child' => 'orderDelete',
            'parent' => 'timetableView'
        ]);

        $this->delete('crm_auth_item_child', [
            'child' => 'timetableView',
            'parent' => 'company'
        ]);

        $this->delete('crm_auth_item_child', [
            'child' => 'timetableView',
            'parent' => 'company'
        ]);

        $this->delete('crm_auth_item', ['name' => 'timetableView']);
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
