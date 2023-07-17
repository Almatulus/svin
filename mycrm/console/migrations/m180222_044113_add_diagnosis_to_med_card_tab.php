<?php

use yii\db\Migration;

/**
 * Class m180222_044113_add_diagnosis_to_med_card_tab
 */
class m180222_044113_add_diagnosis_to_med_card_tab extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('{{%med_card_tabs}}', 'diagnosis_id', $this->integer()->unsigned());

        $this->addForeignKey('fk_med_card_tab_diagnosis', '{{%med_card_tabs}}', 'diagnosis_id',
            '{{%med_card_diagnoses}}', 'id');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn('{{%med_card_tabs}}', 'diagnosis_id');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180222_044113_add_diagnosis_to_med_card_tab cannot be reverted.\n";

        return false;
    }
    */
}
