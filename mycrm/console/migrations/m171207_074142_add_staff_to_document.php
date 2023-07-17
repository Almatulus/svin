<?php

use yii\db\Migration;

/**
 * Class m171207_074142_add_staff_to_document
 */
class m171207_074142_add_staff_to_document extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('{{%documents}}', 'staff_id', $this->integer()->notNull()->unsigned());
        $this->addColumn('{{%documents}}', 'manager_id', $this->integer()->unsigned());
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn('{{%documents}}', 'staff_id');
        $this->dropColumn('{{%documents}}', 'manager_id');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m171207_074142_add_staff_to_document cannot be reverted.\n";

        return false;
    }
    */
}
