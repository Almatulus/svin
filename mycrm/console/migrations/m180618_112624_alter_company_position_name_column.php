<?php

use yii\db\Migration;

/**
 * Class m180618_112624_alter_company_position_name_column
 */
class m180618_112624_alter_company_position_name_column extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn("{{%company_positions}}", 'name', $this->string(511));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->alterColumn("{{%company_positions}}", 'name', $this->string(255));
    }
}
