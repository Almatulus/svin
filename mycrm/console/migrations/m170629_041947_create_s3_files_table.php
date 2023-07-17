<?php

use yii\db\Migration;

/**
 * Handles the creation of table `s3_files`.
 */
class m170629_041947_create_s3_files_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTable('{{%s3_files}}', [
            'id' => $this->primaryKey(),
            'path' => $this->string()->notNull()->unique()
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTable('{{%s3_files}}');
    }
}
