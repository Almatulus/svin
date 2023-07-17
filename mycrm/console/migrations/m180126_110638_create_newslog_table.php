<?php

use yii\db\Migration;

/**
 * Handles the creation of table `newslog`.
 */
class m180126_110638_create_newslog_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('{{%news_logs}}', [
            'id' => $this->primaryKey(),
            'link' => $this->string(255),
            'text' => $this->text(),
            'status' => $this->smallInteger()->notNull()->defaultValue(0),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('{{%news_logs}}');
    }
}
