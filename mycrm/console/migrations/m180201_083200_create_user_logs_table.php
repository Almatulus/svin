<?php

use yii\db\Migration;

/**
 * Handles the creation of table `user_logs`.
 */
class m180201_083200_create_user_logs_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('{{%user_logs}}', [
            'id'         => $this->primaryKey(),
            'user_id'    => $this->integer()->unsigned()->notNull(),
            'ip_address' => $this->string()->notNull(),
            'user_agent' => $this->string()->notNull(),
            'datetime'   => $this->dateTime()->notNull(),
            'action'     => $this->integer()->unsigned()->notNull()
        ]);

        $this->addForeignKey('fk-user', '{{%user_logs}}', 'user_id', '{{%users}}', 'id');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('{{%user_logs}}');
    }
}
