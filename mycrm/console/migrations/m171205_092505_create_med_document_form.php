<?php

use yii\db\Migration;

/**
 * Class m171205_092505_create_med_document_form
 */
class m171205_092505_create_med_document_form extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTable('{{%document_forms}}', [
            'id'              => $this->primaryKey(),
            'name'            => $this->string()->notNull(),
            'has_dental_card' => $this->boolean()->defaultValue(false)->notNull(),
            'doc_path'        => $this->string(),
        ]);

        $this->createTable('{{%document_form_groups}}', [
            'id'               => $this->primaryKey(),
            'order'            => $this->integer()->defaultValue(1),
            'label'            => $this->string()->notNull(),
            'document_form_id' => $this->integer()->unsigned()->notNull(),
        ]);

        $this->addForeignKey('fk-document-form', '{{%document_form_groups}}', 'document_form_id',
            '{{%document_forms}}', 'id', 'CASCADE', 'CASCADE');

        $this->createTable('{{%document_form_elements}}', [
            'id'                     => $this->primaryKey(),
            'order'                  => $this->integer()->defaultValue(1),
            'label'                  => $this->string()->notNull(),
            'key'                    => $this->string()->unique()->notNull(),
            'type'                   => $this->integer()->notNull(),
            'options'                => "TEXT[]",
            'document_form_id'       => $this->integer()->unsigned()->notNull(),
            'document_form_group_id' => $this->integer()->unsigned()
        ]);

        $this->addForeignKey('fk-document-form', '{{%document_form_elements}}', 'document_form_id',
            '{{%document_forms}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk-document-form-group', '{{%document_form_elements}}', 'document_form_group_id',
            '{{%document_form_groups}}', 'id', 'CASCADE', 'CASCADE');

        $this->createTable('{{%documents}}', [
            'id'                  => $this->primaryKey(),
            'document_form_id'    => $this->integer()->notNull()->unsigned(),
            'company_customer_id' => $this->integer()->notNull()->unsigned(),
            'dental_card'         => 'jsonb',
            'created_at'          => $this->dateTime()->notNull(),
            'updated_at'          => $this->dateTime()->notNull(),
        ]);

        $this->addForeignKey('fk-document-form', '{{%documents}}', 'document_form_id',
            '{{%document_forms}}', 'id');
        $this->addForeignKey('fk-company-customer', '{{%documents}}', 'company_customer_id',
            '{{%company_customers}}', 'id');

        $this->createTable('{{%document_values}}', [
            'document_id'              => $this->integer()->notNull()->unsigned(),
            'document_form_element_id' => $this->integer()->notNull()->unsigned(),
            'value'                    => $this->string()->notNull()
        ]);

        $this->addPrimaryKey($this->db->tablePrefix . 'document_values_pkey', '{{%document_values}}',
            ['document_id', 'document_form_element_id']
        );
        $this->addForeignKey('fk-document', '{{%document_values}}', 'document_id',
            '{{%documents}}', 'id');
        $this->addForeignKey('fk-document-form-element', '{{%document_values}}', 'document_form_element_id',
            '{{%document_form_elements}}', 'id');

//        $this->createTable('{{%document_dental_card_elements}}', [
//            'id' => $this->primaryKey(),
//            'document_id' => $this->integer()->notNull()->unsigned(),
//            'tooth' => $this->integer()->notNull(),
//            'diagnosis' => $this->integer()->notNull(),
//            'mobility' => $this->integer()
//        ]);
//
//        $this->addForeignKey('fk-document', '{{%document_dental_card_elements}}', 'document_id',
//            '{{%documents}}', 'id');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTable('{{%document_values}}');
//        $this->dropTable('{{%document_dental_card_elements}}');
        $this->dropTable('{{%documents}}');
        $this->dropTable('{{%document_form_elements}}');
        $this->dropTable('{{%document_form_groups}}');
        $this->dropTable('{{%document_forms}}');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m171205_092505_create_med_document_form cannot be reverted.\n";

        return false;
    }
    */
}
