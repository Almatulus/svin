<?php

use yii\db\Migration;

class m170109_051717_add_company_balance extends Migration
{
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
        $this->addColumn('{{%companies}}', 'balance', $this->double()->defaultValue(0));
    }

    public function safeDown()
    {
        $this->dropColumn('{{%companies}}', 'balance');
    }
}
