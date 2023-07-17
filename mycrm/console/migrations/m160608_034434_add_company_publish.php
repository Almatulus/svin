<?php

use yii\db\Migration;

class m160608_034434_add_company_publish extends Migration
{
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
        $this->addColumn("crm_companies", "publish", $this->boolean()->notNull()->defaultValue(true));
    }

    public function safeDown()
    {
        $this->dropColumn("crm_companies", "publish");
    }
}
