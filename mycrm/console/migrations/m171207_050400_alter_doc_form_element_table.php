<?php

use yii\db\Migration;

/**
 * Class m171207_050400_alter_doc_form_element_table
 */
class m171207_050400_alter_doc_form_element_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->execute('ALTER TABLE crm_document_form_elements DROP CONSTRAINT crm_document_form_elements_key_key;');
        $this->createIndex('crm_document_form_elements_key_idx', '{{%document_form_elements}}', [
            'document_form_id',
            'key'
        ], true);
        $this->execute('ALTER TABLE crm_document_form_elements ADD CONSTRAINT crm_document_form_elements_key_key UNIQUE USING INDEX crm_document_form_elements_key_idx;');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->execute('ALTER TABLE crm_document_form_elements DROP CONSTRAINT crm_document_form_elements_key_key;');
        $this->createIndex('crm_document_form_elements_key_idx', '{{%document_form_elements}}', [
            'key'
        ], true);
        $this->execute('ALTER TABLE crm_document_form_elements ADD CONSTRAINT crm_document_form_elements_key_key UNIQUE USING INDEX crm_document_form_elements_key_idx;');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m171207_050400_alter_doc_form_element_table cannot be reverted.\n";

        return false;
    }
    */
}
