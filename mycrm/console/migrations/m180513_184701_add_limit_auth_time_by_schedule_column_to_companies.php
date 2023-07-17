<?php

use yii\db\Migration;

/**
 * Class m180513_184701_add_limit_auth_time_by_schedule_column_to_companies
 */
class m180513_184701_add_limit_auth_time_by_schedule_column_to_companies extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%companies}}', 'limit_auth_time_by_schedule', $this->boolean()->defaultValue(false));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%companies}}', 'limit_auth_time_by_schedule');
    }
}
