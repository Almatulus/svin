<?php

use core\models\division\DivisionService;
use yii\db\Migration;

class m160613_105223_division_service_status extends Migration
{
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
        $this->addColumn("crm_division_services", "status", $this->integer()->notNull()->defaultValue(DivisionService::STATUS_ENABLED));
    }

    public function safeDown()
    {
        $this->dropColumn("crm_division_services", "status");
    }
}
