<?php

use yii\db\Migration;

/**
 * Handles the creation of table `confirm_key_request`.
 */
class m170911_130005_create_confirm_key_request_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTable('{{%confirm_keys}}', [
            'id' => $this->primaryKey(),
            'code' => $this->string()->notNull()->unique(),
            'username' => $this->string()->notNull(),
            'status' => $this->integer()->unsigned()->notNull()->defaultValue(1),
            'expired_at' => $this->timestamp()->notNull(),
        ]);

        $this->createIndex('ix_confirm_key_username', '{{%confirm_keys}}', 'username');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTable('{{%confirm_keys}}');
    }
}
