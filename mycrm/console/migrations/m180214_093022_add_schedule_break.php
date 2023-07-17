<?php

use yii\db\Migration;

/**
 * Class m180214_093022_add_schedule_break
 */
class m180214_093022_add_schedule_break extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('{{%staff_schedules}}', 'break_start', $this->dateTime());
        $this->addColumn('{{%staff_schedules}}', 'break_end', $this->dateTime());
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn('{{%staff_schedules}}', 'break_start');
        $this->dropColumn('{{%staff_schedules}}', 'break_end');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180214_093022_add_schedule_break cannot be reverted.\n";

        return false;
    }
    */
}
