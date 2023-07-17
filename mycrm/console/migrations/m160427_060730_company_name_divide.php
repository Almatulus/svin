<?php

use yii\db\Migration;

class m160427_060730_company_name_divide extends Migration
{
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
        $this->addColumn('crm_companies', 'head_surname', $this->string());
        $this->addColumn('crm_companies', 'head_patronymic', $this->string());
        $this->renameColumn('crm_companies', 'header_name', 'head_name');
    }

    public function safeDown()
    {
        $this->dropColumn('crm_companies', 'head_surname');
        $this->dropColumn('crm_companies', 'head_patronymic');
        $this->renameColumn('crm_companies', 'head_name', 'header_name');
    }
}
