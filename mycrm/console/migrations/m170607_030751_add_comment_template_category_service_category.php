<?php

use yii\db\Migration;

class m170607_030751_add_comment_template_category_service_category extends Migration
{
    public function safeUp()
    {
        $this->delete('{{%comment_template_categories}}');
        $this->addColumn('{{%comment_template_categories}}', 'service_category_id', $this->integer()->unsigned()->notNull());
    }

    public function safeDown()
    {
        $this->dropColumn('{{%comment_template_categories}}', 'service_category_id');
    }
}
