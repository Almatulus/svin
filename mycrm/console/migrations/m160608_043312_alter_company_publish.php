<?php

use yii\db\Migration;

class m160608_043312_alter_company_publish extends Migration
{
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
        $this->dropColumn("crm_companies", "publish");
        $this->addColumn("crm_companies", "publish", $this->integer()->notNull()->defaultValue(1));
    }

    public function safeDown()
    {
        $this->dropColumn("crm_companies", "publish");
        $this->addColumn("crm_companies", "publish", $this->boolean()->notNull()->defaultValue(true));
    }
}
