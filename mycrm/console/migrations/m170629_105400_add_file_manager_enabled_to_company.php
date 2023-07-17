<?php

use yii\db\Migration;

class m170629_105400_add_file_manager_enabled_to_company extends Migration
{
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
        $this->addColumn('{{%companies}}', 'file_manager_enabled', $this->boolean()->defaultValue(false));
    }

    public function safeDown()
    {
        $this->dropColumn('{{%companies}}', 'file_manager_enabled');
    }
}
