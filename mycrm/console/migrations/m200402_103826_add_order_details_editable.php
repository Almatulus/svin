<?php

use yii\db\Migration;

/**
 * Class m200402_103826_add_order_details_editable
 */
class m200402_103826_add_order_details_editable extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn("crm_orders", "services_disabled", $this->boolean()->defaultValue(false));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn("crm_orders", "services_disabled");
    }
}
