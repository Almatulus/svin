<?php

use yii\db\Migration;

class m160709_152655_add_payment_columns extends Migration
{
    public function up()
    {
        $this->addColumn('crm_staffs', 'payment_date', $this->date());
        $this->addColumn('crm_orders', 'is_paid', $this->boolean()->notNull()->defaultValue(false));
    }

    public function down()
    {
        $this->dropColumn('crm_orders', 'is_paid');
        $this->dropColumn('crm_staffs', 'payment_date');
    }
}
