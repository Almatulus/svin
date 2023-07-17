<?php

use yii\db\Migration;
use yii\db\Schema;

class m160225_040231_alter_division_table extends Migration
{
    public function up()
    {
        $this->addColumn('crm_divisions', 'rating', Schema::TYPE_DOUBLE.' NOT NULL DEFAULT 0');
        $this->addColumn('crm_divisions', 'position_lat', Schema::TYPE_DOUBLE.' NOT NULL DEFAULT 0');
        $this->addColumn('crm_divisions', 'position_lon', Schema::TYPE_DOUBLE.' NOT NULL DEFAULT 0');
        $this->addColumn('crm_divisions', 'working_start', Schema::TYPE_TIME . " NOT NULL DEFAULT NOW()");
        $this->addColumn('crm_divisions', 'working_finish', Schema::TYPE_TIME . " NOT NULL DEFAULT NOW()");
    }

    public function down()
    {
        $this->dropColumn('crm_divisions','rating');
        $this->dropColumn('crm_divisions', 'position_lat');
        $this->dropColumn('crm_divisions', 'position_lon');
        $this->dropColumn('crm_divisions', 'working_start');
        $this->dropColumn('crm_divisions', 'working_finish');
    }
}
