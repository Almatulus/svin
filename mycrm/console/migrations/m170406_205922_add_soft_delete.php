<?php

use yii\db\Migration;

class m170406_205922_add_soft_delete extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%order_services}}', 'deleted_time', $this->dateTime()->defaultValue(null));
        $this->addColumn('{{%order_service_products}}', 'deleted_time', $this->dateTime()->defaultValue(null));
    }

    public function safeDown()
    {
        $this->dropColumn('{{%order_services}}', 'deleted_time');
        $this->dropColumn('{{%order_service_products}}', 'deleted_time');
    }
}
