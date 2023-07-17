<?php

use yii\db\Migration;

/**
 * Class m220430_093232_create_document_suggestions
 */
class m220430_093232_create_document_suggestions extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%document_suggestions}}', [
            'id'         => $this->primaryKey(),
            'text'       => $this->text(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%document_suggestions}}');
    }
}
