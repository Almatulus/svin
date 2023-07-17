<?php

use yii\db\Migration;

class m170210_124503_add_webcall_log_action extends Migration
{
    public function safeUp()
    {
        $this->addColumn("{{%company_webcalls_log}}", "action", $this->string()->notNull());
    }

    public function safeDown()
    {
        $this->dropColumn("{{%company_webcalls_log}}", "action");
    }
}
