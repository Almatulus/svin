<?php

use yii\db\Migration;

class m160602_222403_add_category_ordering extends Migration
{
    public function safeUp()
    {
        $this->addColumn("crm_service_categories", "order", $this->integer()->notNull()->defaultValue(0));
    }

    public function safeDown()
    {
        $this->dropColumn("crm_service_categories", "order");
    }
}
