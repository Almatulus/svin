<?php

use yii\db\Migration;

/**
 * Class m180312_061502_create_document_templates
 */
class m180312_061502_create_document_templates extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTable('{{%document_templates}}', [
            'id'               => $this->primaryKey(),
            'document_form_id' => $this->integer()->unsigned()->notNull(),
            'name'             => $this->string()->notNull(),
            'values'           => 'jsonb',
            'created_by'       => $this->integer()->unsigned()->notNull(),
            'created_at'       => $this->dateTime()->notNull()
        ]);

        $this->addForeignKey('fk_document_template_form', '{{%document_templates}}', 'document_form_id',
            '{{%document_forms}}', 'id');
        $this->addForeignKey('fk_document_template_creator', '{{%document_templates}}', 'created_by',
            '{{%users}}', 'id');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTable('{{%document_templates}}');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180312_061502_create_document_templates cannot be reverted.\n";

        return false;
    }
    */
}
