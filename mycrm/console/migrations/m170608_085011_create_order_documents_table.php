<?php

use yii\db\Migration;

/**
 * Handles the creation of table `order_documents`.
 */
class m170608_085011_create_order_documents_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('{{%order_documents}}', [
            'id' => $this->primaryKey(),
            'date' => $this->dateTime()->notNull(),
            'order_id' => $this->integer()->notNull(),
            'path' => $this->string()->notNull(),
            'template_id' => $this->integer()->notNull(),
            'user_id' => $this->integer()->notNull()
        ]);

        $this->addForeignKey('fk_order_document_order', '{{%order_documents}}', 'order_id', '{{%orders}}', 'id');
        $this->addForeignKey('fk_order_document_user', '{{%order_documents}}', 'user_id', '{{%users}}', 'id');

        $this->createIndex('order_documents_date', '{{%order_documents}}', 'date');
        $this->createIndex('order_documents_order_id', '{{%order_documents}}', 'order_id');
        $this->createIndex('order_documents_user_id', '{{%order_documents}}', 'user_id');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('{{%order_documents}}');
    }
}
