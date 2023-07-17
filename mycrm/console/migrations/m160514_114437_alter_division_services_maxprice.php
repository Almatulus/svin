<?php

use yii\db\Migration;

class m160514_114437_alter_division_services_maxprice extends Migration
{
    public function up()
    {
        $this->addColumn('crm_division_services', 'price_max', $this->integer());
    }

    public function down()
    {
        $this->dropColumn('crm_division_services', 'price_max');
    }
}
