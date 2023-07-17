<?php

use yii\db\Migration;

class m160909_084127_alter_order_statuses extends Migration
{
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
        $this->update("crm_orders", ['status' => \core\models\order\Order::STATUS_ENABLED], ['status' => 2]);
        $this->update("crm_order_history", ['status' => \core\models\order\Order::STATUS_ENABLED], ['status' => 2]);
    }

    public function safeDown()
    {
    }
}
