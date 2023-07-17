<?php

use yii\db\Migration;

class m170628_092655_add_widget_link_prefix extends Migration
{
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
        $this->addColumn('{{%companies}}', 'widget_prefix', $this->string()->unique());
        $this->createIndex('companies_widget_prefix_idx', '{{%companies}}', 'widget_prefix');
    }

    public function safeDown()
    {
        $this->dropColumn('{{%companies}}', 'widget_prefix');
    }
}
