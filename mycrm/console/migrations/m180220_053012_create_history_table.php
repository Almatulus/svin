<?php

use yii\db\Migration;

/**
 * Handles the creation of table `history`.
 */
class m180220_053012_create_history_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('{{%history}}', [
            'id' => $this->primaryKey(),
            'initiator' => $this->integer(), // user_id
            'ip' => $this->string(32),
            'event' => $this->string(255),
            'class' => $this->string(255),
            'table_name' => $this->string(64),
            'row_id' => $this->string(32),
            'log' => 'JSON',
            'created_time' => $this->dateTime()->notNull(),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('{{%history}}');
    }
}
