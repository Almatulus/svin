<?php

use yii\db\Migration;

class m170830_210116_add_company_interval extends Migration
{
    public function safeUp()
    {
        $this->addColumn(
            '{{%companies}}',
            'interval',
            $this->integer()->unsigned()->notNull()->defaultValue(5)
        );
    }

    public function safeDown()
    {
        $this->dropColumn('{{%companies}}', 'interval');
    }
}
