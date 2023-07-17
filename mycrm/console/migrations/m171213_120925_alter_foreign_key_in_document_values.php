<?php

use yii\db\Migration;

/**
 * Class m171213_120925_alter_foreign_key_in_document_values
 */
class m171213_120925_alter_foreign_key_in_document_values extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->dropForeignKey('fk-document-form-element', '{{%document_values}}');
        $this->addForeignKey('fk-document-form-element', '{{%document_values}}', 'document_form_element_id',
            '{{%document_form_elements}}', 'id', "cascade", "cascade");
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-document-form-element', '{{%document_values}}');
        $this->addForeignKey('fk-document-form-element', '{{%document_values}}', 'document_form_element_id',
            '{{%document_form_elements}}', 'id');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m171213_120925_alter_foreign_key_in_document_values cannot be reverted.\n";

        return false;
    }
    */
}
