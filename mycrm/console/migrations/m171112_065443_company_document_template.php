<?php

use yii\db\Migration;

/**
 * Class m171112_065443_company_document_template
 */
class m171112_065443_company_document_template extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('{{%order_document_templates}}', 'company_id', $this->integer()->unsigned());
        $this->addColumn('{{%order_document_templates}}', 'path', $this->string());

        $this->addForeignKey(
            'fk_order_document_templates_company',
            '{{%order_document_templates}}',
            'company_id',
            '{{%companies}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn('{{%order_document_templates}}', 'path');
        $this->dropColumn('{{%order_document_templates}}', 'company_id');
    }
}
