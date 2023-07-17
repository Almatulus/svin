<?php

use yii\db\Migration;

/**
 * Class m171221_190821_add_document_form_element_raw
 */
class m171221_190821_add_document_form_element_raw extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn(
            '{{%document_form_elements}}',
            'raw_id',
            $this->integer()->notNull()->unsigned()->defaultValue(1)
        );
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn('{{%document_form_elements}}', 'raw_id');
    }
}
