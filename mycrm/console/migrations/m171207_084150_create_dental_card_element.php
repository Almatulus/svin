<?php

use yii\db\Migration;

/**
 * Class m171207_084150_create_dental_card_element
 */
class m171207_084150_create_dental_card_element extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTable('{{%document_dental_card_elements}}', [
            'document_id'  => $this->integer()->notNull()->unsigned(),
            'number'       => $this->integer()->notNull(),
            'diagnosis_id' => $this->integer()->notNull(),
            'mobility'     => $this->integer()
        ]);

        $this->addPrimaryKey($this->db->tablePrefix . 'document_dental_card_elements_pkey',
            '{{%document_dental_card_elements}}',
            [
                'document_id',
                'number'
            ]
        );
        $this->addForeignKey('fk-document', '{{%document_dental_card_elements}}', 'document_id',
            '{{%documents}}', 'id');
        $this->addForeignKey('fk-diagnosis', '{{%document_dental_card_elements}}', 'diagnosis_id',
            '{{%med_card_teeth_diagnoses}}', 'id');

        $this->dropColumn('{{%documents}}', 'dental_card');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTable('{{%document_dental_card_elements}}');
        $this->addColumn('{{%documents}}', 'dental_card', 'jsonb');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m171207_084150_create_dental_card_element cannot be reverted.\n";

        return false;
    }
    */
}
