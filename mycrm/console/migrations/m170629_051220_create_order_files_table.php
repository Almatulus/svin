<?php

use yii\db\Migration;

/**
 * Handles the creation of table `order_files`.
 */
class m170629_051220_create_order_files_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTable('{{%order_files}}', [
            'order_id' => $this->integer()->notNull(),
            'file_id' => $this->integer()->notNull()
        ]);

        $this->addPrimaryKey('order_files_pk', '{{%order_files}}', ['order_id', 'file_id']);

        $this->addForeignKey('fk_order_file_order', '{{%order_files}}', 'order_id', '{{%orders}}', 'id');
        $this->addForeignKey('fk_order_file_file', '{{%order_files}}', 'file_id', '{{%s3_files}}', 'id');

        $this->createIndex('order_files_order_id_idx', '{{%order_files}}', 'order_id');
        $this->createIndex('order_files_file_id_idx', '{{%order_files}}', 'file_id');
        $this->createIndex('uq_order_files', '{{%order_files}}', ['order_id', 'file_id'], true);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk_order_file_order', '{{%order_files}}');
        $this->dropForeignKey('fk_order_file_file', '{{%order_files}}');

        $this->dropTable('{{%order_files}}');
    }
}
