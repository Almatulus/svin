<?php

use core\models\ServiceCategory;
use yii\db\Migration;

/**
 * Handles the creation of table `document_templates`.
 */
class m170619_112930_create_document_templates_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $templates = [
            1 => 'Согласие на отбеливание',
            2 => 'Договор на проведение операции по дентальной имплантации зубов',
            3 => 'Договор оказания стоматологических услуг в клинике',
            4 => 'Согласие на лечение кариеса',
            5 => 'Согласие на ортодонт. лечение',
            6 => 'Согласие на ортопед. лечение',
            7 => 'Согласие на парадонт. лечение',
            8 => 'Согласие на проф. гигиену',
            9 => 'Согласие на хирургическое лечение',
            10 => 'Согласие на эндодонт лечение'
        ];

        $this->createTable('{{%order_document_templates}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull(),
            'filename' => $this->string()->notNull(),
            'category_id' => $this->integer()->notNull()
        ]);

        $this->addForeignKey('fk_document_template_category', '{{%order_document_templates}}', 'category_id',
            '{{%service_categories}}', 'id');
        $this->createIndex('order_document_templates_category_id', '{{%order_document_templates}}', 'category_id');

        foreach ($templates as $key => $templateName) {
            $this->insert('{{%order_document_templates}}', [
                'name' => $templateName,
                'filename' => str_replace(" ", "_", $templateName) . ".docx",
                'category_id' => ServiceCategory::ROOT_STOMATOLOGY
            ]);
        }

        $this->addForeignKey('fk_order_document_template', '{{%order_documents}}', 'template_id',
            '{{%order_document_templates}}', 'id');

    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk_order_document_template', '{{%order_documents}}');
        $this->dropTable('{{%order_document_templates}}');
    }
}
