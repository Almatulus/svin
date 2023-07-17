<?php

use yii\db\Migration;

/**
 * Class m180418_095333_modify_document_forms
 */
class m180418_095333_modify_document_forms extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%document_services}}', [
            'document_id' => $this->integer()->unsigned()->notNull(),
            'service_id'  => $this->integer()->unsigned()->notNull(),
            'quantity'    => $this->integer()->unsigned()->notNull(),
            'price'       => $this->integer()->unsigned()->notNull(),
            'discount'    => $this->integer()->unsigned()->notNull()
        ]);

        $this->addPrimaryKey($this->db->tablePrefix . 'document_services_pkey', '{{%document_services}}', [
            'document_id',
            'service_id'
        ]);
        $this->addForeignKey('fk_document_services_document', '{{%document_services}}', 'document_id',
            '{{%documents}}', 'id');
        $this->addForeignKey('fk_document_services_service', '{{%document_services}}', 'service_id',
            '{{%division_services}}', 'id');

        $this->addColumn('{{%document_forms}}', 'has_services', $this->boolean()->defaultValue(false));

        $this->addColumn('{{%document_form_elements}}', 'search_url', $this->string());
        $this->addColumn('{{%document_form_elements}}', 'depends_on', $this->string());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%document_services}}');

        $this->dropColumn('{{%document_forms}}', 'has_services');

        $this->dropColumn('{{%document_form_elements}}', 'search_url');
        $this->dropColumn('{{%document_form_elements}}', 'depends_on');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180418_095333_modify_document_forms cannot be reverted.\n";

        return false;
    }
    */
}
