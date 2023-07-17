<?php

use yii\db\Migration;

class m170619_094829_add_status_to_division_phone extends Migration
{
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
        $this->addColumn('{{%division_phones}}', 'status', $this->integer()->notNull()->defaultValue(1));
    }

    public function safeDown()
    {
        $this->dropColumn('{{%division_phones}}', 'status');
    }

}
