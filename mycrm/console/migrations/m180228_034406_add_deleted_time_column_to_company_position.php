<?php

use yii\db\Migration;

/**
 * Class m180228_034406_add_deleted_time_column_to_company_position
 */
class m180228_034406_add_deleted_time_column_to_company_position extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('{{%company_positions}}', 'deleted_time', $this->dateTime()->defaultValue(null));
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn('{{%company_positions}}', 'deleted_time');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180228_034406_add_deleted_time_column_to_company_position cannot be reverted.\n";

        return false;
    }
    */
}
