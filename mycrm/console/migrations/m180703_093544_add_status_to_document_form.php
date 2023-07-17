<?php

use yii\db\Migration;

/**
 * Class m180703_093544_add_status_to_document_form
 */
class m180703_093544_add_status_to_document_form extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%document_forms}}', 'enabled', $this->boolean()->defaultValue(true));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%document_forms}}', 'enabled');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180703_093544_add_status_to_document_form cannot be reverted.\n";

        return false;
    }
    */
}
