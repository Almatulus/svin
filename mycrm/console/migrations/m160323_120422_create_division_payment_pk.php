<?php

use yii\db\Migration;

class m160323_120422_create_division_payment_pk extends Migration
{
    public function up()
    {
        $this->delete("crm_division_payments");
        $this->addColumn("crm_division_payments", "id", $this->primaryKey());
    }

    public function down()
    {
        $this->dropColumn("crm_division_payments", "id");
    }
}
