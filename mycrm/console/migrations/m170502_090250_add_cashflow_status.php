<?php

use yii\db\Migration;

class m170502_090250_add_cashflow_status extends Migration
{

    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
        $this->addColumn("crm_company_cashflows", "status", $this->integer()->notNull()->defaultValue(1));
    }

    public function safeDown()
    {
        $this->dropColumn("crm_company_cashflows", "status");
    }

}
