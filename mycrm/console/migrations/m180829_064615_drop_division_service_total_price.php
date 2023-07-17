<?php

use core\models\order\OrderService;
use yii\db\Migration;

/**
 * Class m180829_064615_drop_division_service_total_price
 */
class m180829_064615_drop_division_service_total_price extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropColumn(OrderService::tableName(), 'total_price');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->addColumn(OrderService::tableName(), 'total_price', $this->integer()->notNull()->defaultValue(0));
        $this->execute('UPDATE ' . OrderService::tableName() . ' SET total_price = price*(100-discount)/100');
    }
}
