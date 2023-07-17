<?php

use yii\db\Migration;

class m170528_153412_add_order_history_user extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%order_history}}', 'acting_user', $this->string());
    }

    public function safeDown()
    {
        $this->dropColumn('{{%order_history}}', 'acting_user');
    }
}
