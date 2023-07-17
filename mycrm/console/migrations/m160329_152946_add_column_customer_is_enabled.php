<?php

use yii\db\Migration;

class m160329_152946_add_column_customer_is_enabled extends Migration
{
    public function safeUp()
    {
        $this->addColumn('crm_company_customers','is_active',$this->boolean()->notNull()->defaultValue(true));
    }

    public function safeDown()
    {
        $this->dropColumn('crm_company_customers','is_active');
    }
}
