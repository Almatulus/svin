<?php

use yii\db\Migration;

class m170823_122353_remove_order_key extends Migration
{
    public function safeUp()
    {
        $this->dropColumn('{{%orders}}', 'key');
    }

    public function safeDown()
    {
        $this->addColumn(
            '{{%orders}}',
            'key',
            $this->string()->notNull()->unique()
        );
    }
}
