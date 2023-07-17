<?php

use yii\db\Migration;

class m160602_225704_add_order_cash extends Migration
{
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
        $this->addColumn("crm_orders", "company_cash_id", $this->integer());
        $this->addForeignKey("fk_orders_company_cash", "crm_orders", "company_cash_id", "crm_company_cashes", "id");
    }

    public function safeDown()
    {
        $this->dropColumn("crm_orders", "company_cash_id");
    }
}
