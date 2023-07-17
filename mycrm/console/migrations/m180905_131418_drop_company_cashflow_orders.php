<?php

use core\models\finance\CompanyCashflow;
use core\models\order\Order;
use yii\db\Migration;

/**
 * Class m180905_131418_drop_company_cashflow_orders
 */
class m180905_131418_drop_company_cashflow_orders extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn(
            CompanyCashflow::tableName(),
            'order_id',
            $this->integer()->defaultValue(null)
        );
        $this->addForeignKey(
            'fk_company_cashflow_order',
            CompanyCashflow::tableName(),
            'order_id',
            Order::tableName(),
            'id'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn(CompanyCashflow::tableName(), 'order_id');
    }
}
