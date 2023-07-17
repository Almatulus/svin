<?php

use yii\db\Migration;

class m171102_161739_remove_user_tokens extends Migration
{
    public function safeUp()
    {
        $this->dropTable('{{%user_tokens}}');
    }

    public function safeDown()
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
}
