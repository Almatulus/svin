<?php

use yii\db\Migration;

class m170605_013415_create_comment_templates extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%comment_template_categories}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull(),
            'parent_id' => $this->integer()->unsigned(),
        ]);
        $this->addForeignKey(
            'fk_comment_template_category_parent',
            '{{%comment_template_categories}}',
            'parent_id',
            '{{%comment_template_categories}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
        $this->createTable('{{%comment_templates}}', [
            'id' => $this->primaryKey(),
            'comment' => $this->text(),
            'category_id' => $this->integer()->unsigned()->notNull(),
        ]);
        $this->addForeignKey(
            'fk_comment_template_category',
            '{{%comment_templates}}',
            'category_id',
            '{{%comment_template_categories}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropTable('{{%comment_templates}}');
        $this->dropTable('{{%comment_template_categories}}');
    }
}
