<?php

use yii\db\Migration;

class m170911_102719_create_token extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%user_tokens}}', [
            'id'         => $this->primaryKey(),
            'user_id'    => $this->integer()->unsigned()->notNull(),
            'token'      => $this->string()->notNull()->unique(),
            'expired_at' => $this->timestamp()->notNull()
        ]);

        $this->addForeignKey(
            'fk_user_tokens_user',
            '{{%user_tokens}}',
            'user_id',
            '{{%users}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropTable('{{%user_tokens}}');
    }
}
