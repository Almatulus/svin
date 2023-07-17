<?php

use yii\db\Migration;

class m161007_043812_add_statistics_permission extends Migration
{
    public function up()
    {
        $time = time();

        $this->insert('crm_auth_item', [
            'name' => 'statisticView',
            'type' => 2,
            'created_at' => $time,
            'updated_at' => $time
        ]);

        $this->insert('crm_auth_item_child', [
            'child' => 'statisticView',
            'parent' => 'administrator'
        ]);

        $this->insert('crm_auth_item_child', [
            'child' => 'statisticView',
            'parent' => 'company'
        ]);
    }

    public function down()
    {
        $this->delete('crm_auth_item_child', [
            'child' => 'statisticView',
            'parent' => 'administrator'
        ]);

        $this->delete('crm_auth_item_child', [
            'child' => 'statisticView',
            'parent' => 'company'
        ]);

        $this->delete('crm_auth_item', ['name' => 'statisticView']);
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
