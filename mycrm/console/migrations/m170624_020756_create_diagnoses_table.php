<?php

use yii\db\Migration;

/**
 * Handles the creation of table `comment_diagnoses`.
 */
class m170624_020756_create_diagnoses_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTable('{{%diagnoses}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull(),
        ]);

        $this->createTable('{{%comment_template_diagnosis_map}}', [
            'comment_template_id' => $this->integer()->unsigned()->notNull(),
            'diagnosis_id' => $this->integer()->unsigned()->notNull(),
        ]);

        $this->addForeignKey(
            'fk_comment_template_diagnoses_comment_template',
            '{{%comment_template_diagnosis_map}}',
            'comment_template_id',
            '{{%comment_templates}}', 'id',
            'SET NULL',
            'SET NULL'
        );

        $this->addForeignKey(
            'fk_comment_template_diagnoses_diagnosis',
            '{{%comment_template_diagnosis_map}}',
            'diagnosis_id',
            '{{%diagnoses}}', 'id',
            'SET NULL',
            'SET NULL'
        );
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTable('{{%comment_template_diagnosis_map}}');
        $this->dropTable('{{%diagnoses}}');
    }
}
