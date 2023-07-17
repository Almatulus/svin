<?php

use yii\db\Schema;
use yii\db\Migration;

class m160213_100214_alter_column_gender extends Migration
{
    public function safeUp()
    {
        $this->addColumn('crm_customers', 'gender_new', Schema::TYPE_SMALLINT.' NOT NULL DEFAULT 0');
        $this->update('crm_customers',['gender_new' => 0], ['gender' => null]);
        $this->update('crm_customers',['gender_new' => 1], ['gender' => true]);
        $this->update('crm_customers',['gender_new' => 2], ['gender' => false]);
        $this->dropColumn('crm_customers', 'gender');
        $this->renameColumn('crm_customers', 'gender_new', 'gender');
    }

    public function safeDown()
    {
        $this->addColumn('crm_customers', 'gender_new', Schema::TYPE_BOOLEAN);
        $this->update('crm_customers',['gender_new' => null], ['gender' => 0]);
        $this->update('crm_customers',['gender_new' => true], ['gender' => 1]);
        $this->update('crm_customers',['gender_new' => false], ['gender' => 2]);
        $this->dropColumn('crm_customers', 'gender');
        $this->renameColumn('crm_customers', 'gender_new', 'gender');
    }
}
