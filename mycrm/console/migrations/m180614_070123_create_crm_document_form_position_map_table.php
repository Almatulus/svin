<?php

use yii\db\Migration;

/**
 * Handles the creation of table `crm_document_form_position_map`.
 */
class m180614_070123_create_crm_document_form_position_map_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('crm_document_form_position_map', [
            'document_form_id' => $this->integer()->notNull(),
            'position_id' => $this->integer()->notNull(),
            'PRIMARY KEY (document_form_id, position_id)',
        ]);

        $this->addForeignKey('fk_document_form_position_map_2_document_form', '{{%document_form_position_map}}', 'document_form_id', '{{%document_forms}}', 'id');
        $this->addForeignKey('fk_document_form_position_map_2_position', '{{%document_form_position_map}}', 'position_id', '{{%positions}}', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk_document_form_position_map_2_document_form', '{{%document_form_position_map}}');
        $this->dropForeignKey('fk_document_form_position_map_2_position', '{{%document_form_position_map}}');

        $this->dropTable('crm_document_form_position_map');
    }
}
