<?php

use yii\db\Migration;

class m160929_054335_alter_service_category_table extends Migration
{
    public function up()
    {

        $this->alterColumn('crm_service_categories', 'image_id', "DROP NOT NULL");
        $this->alterColumn('crm_service_categories', 'order', "DROP NOT NULL");

        $this->addColumn('crm_service_categories', 'company_id', $this->integer());
        $this->addColumn('crm_service_categories', 'type', $this->integer()->defaultValue(1));

        $this->addForeignKey('fk_company', 'crm_service_categories', 'company_id', 'crm_companies', 'id');
    }

    public function down()
    {
        $this->dropForeignKey('fk_company', 'crm_service_categories');

        $this->dropColumn('crm_service_categories', 'company_id');
        $this->dropColumn('crm_service_categories', 'type');

        $this->alterColumn('crm_service_categories', 'image_id', "SET NOT NULL");
        $this->alterColumn('crm_service_categories', 'order', "SET NOT NULL");
    }

    /*
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
    }

    public function safeDown()
    {
    }
    */
}
