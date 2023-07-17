<?php

use yii\db\Schema;
use yii\db\Migration;

class m160216_061556_supplement_customers extends Migration
{
    public function safeUp()
    {
        $this->addColumn('crm_customers','sms_birthday',Schema::TYPE_BOOLEAN.' NOT NULL DEFAULT false');
        $this->addColumn('crm_customers','sms_exclude',Schema::TYPE_BOOLEAN.' NOT NULL DEFAULT false');
    }

    public function safeDown()
    {
        $this->dropColumn('crm_customers','sms_birthday');
        $this->dropColumn('crm_customers','sms_exclude');
    }
}
