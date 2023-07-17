<?php

use yii\db\Migration;

/**
 * Handles the creation of table `document_form_company_position_map`.
 */
class m180302_061920_create_document_form_company_position_map_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTable('{{%document_form_company_position_map}}', [
            'document_form_id' => $this->integer()->notNull(),
            'company_position_id' => $this->integer()->notNull(),
            'PRIMARY KEY (document_form_id, company_position_id)',
        ]);

        $this->addForeignKey('fk_document_form_company_position_map_2_document_form', '{{%document_form_company_position_map}}', 'document_form_id', '{{%document_forms}}', 'id');
        $this->addForeignKey('fk_document_form_company_position_map_2_company_position', '{{%document_form_company_position_map}}', 'company_position_id', '{{%company_positions}}', 'id');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk_document_form_company_position_map_2_document_form', '{{%document_form_company_position_map}}');
        $this->dropForeignKey('fk_document_form_company_position_map_2_company_position', '{{%document_form_company_position_map}}');

        $this->dropTable('{{%document_form_company_position_map}}');
    }
}
