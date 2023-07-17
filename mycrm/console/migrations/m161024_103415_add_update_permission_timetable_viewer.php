<?php

use yii\db\Migration;

class m161024_103415_add_update_permission_timetable_viewer extends Migration
{
    public function up()
    {
        $this->insert('crm_auth_item_child', [
            'child' => 'orderUpdate',
            'parent' => 'timetableView'
        ]);
    }

    public function down()
    {
        $this->delete('crm_auth_item_child', [
            'child' => 'orderUpdate',
            'parent' => 'timetableView'
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
