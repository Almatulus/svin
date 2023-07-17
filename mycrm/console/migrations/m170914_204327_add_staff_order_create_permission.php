<?php

use yii\db\Migration;

class m170914_204327_add_staff_order_create_permission extends Migration
{
    public function safeUp()
    {
        $this->addColumn(
            '{{%staffs}}',
            'create_order',
            $this->boolean()->defaultValue(true)->notNull()
        );
    }

    public function safeDown()
    {
        $this->dropColumn('{{%staffs}}', 'create_order');
    }
}
