<?php

use yii\db\Migration;

class m170907_121032_add_company_online_time_limit extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%companies}}', 'online_start', $this->time());
        $this->addColumn('{{%companies}}', 'online_finish', $this->time());
    }

    public function safeDown()
    {
        $this->dropColumn('{{%companies}}', 'online_start');
        $this->dropColumn('{{%companies}}', 'online_finish');
    }
}
