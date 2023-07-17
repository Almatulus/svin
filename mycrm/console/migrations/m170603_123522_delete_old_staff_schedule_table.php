<?php

use yii\db\Migration;

class m170603_123522_delete_old_staff_schedule_table extends Migration
{
    public function safeUp()
    {
        $this->dropTable('{{%staff_schedules_old}}');
    }

    public function safeDown()
    {
    }
}
