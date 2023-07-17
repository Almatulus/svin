<?php

use yii\db\Migration;

/**
 * Handles the creation of table `order_comments`.
 */
class m170607_033241_create_order_comments_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTable('{{%order_comments}}', [
            'id' => $this->primaryKey(),
            'order_id' => $this->integer()->unsigned()->notNull(),
            'category_id' => $this->integer()->unsigned()->notNull(),
            'comment' => $this->string()->notNull(),
            'created_at' => $this->integer()->unsigned(),
            'updated_at' => $this->integer()->unsigned(),
        ]);

        $this->addForeignKey(
            'fk_order_comments_order',
            '{{%order_comments}}',
            'order_id',
            '{{%orders}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_order_comments_category',
            '{{%order_comments}}',
            'category_id',
            '{{%comment_template_categories}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTable('{{%order_comments}}');
    }
}
