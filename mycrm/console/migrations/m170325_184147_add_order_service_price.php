<?php

use yii\db\Migration;

class m170325_184147_add_order_service_price extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%order_services}}', 'price', $this->integer()->notNull()->defaultValue(0));
    }

    public function safeDown()
    {
        $this->dropColumn('{{%order_services}}', 'price');
    }
}
