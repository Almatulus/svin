<?php

use yii\db\Schema;
use yii\db\Migration;

class m160119_170045_alter_table_schedules extends Migration
{
    public function up()
    {
//        $this->createIndex("uq_staff_services_staff_service", "crm_staff_services", ["service_id", "staff_id"]);
//        $this->createTable('crm_customer_requests', [
//            'id' => Schema::TYPE_PK,
//            'type' => Schema::TYPE_INTEGER . ' NOT NULL',
//            'code' => Schema::TYPE_STRING . ' NOT NULL',
//            'created_time' => Schema::TYPE_DATETIME . ' NOT NULL',
//            'customer_id' => Schema::TYPE_INTEGER . ' NOT NULL',
//            'status' => Schema::TYPE_INTEGER . ' NOT NULL DEFAULT 1',
//        ]);
//        $this->addForeignKey('fk_customer_request_customer',
//            'crm_customer_requests', 'customer_id',
//            'crm_customers', 'id');
//        $this->addColumn("crm_companies", "category_id", Schema::TYPE_INTEGER . " NULL");
//        $this->addColumn("crm_staff_schedules", "elapsed_time", Schema::TYPE_INTEGER . " DEFAULT 10 NOT NULL");
//            $this->delete("crm_staff_schedules");
//        $this->createIndex("uq_staff_schedules_datetime_staff_id", "crm_staff_schedules", ["datetime", "staff_id"]);
//        $this->addForeignKey('fk_company_category',
//            'crm_companies', 'category_id',
//            'crm_service_categories', 'id');

        // -----------------------------------------------------------------------------------


//        $this->dropColumn("crm_staff_schedules", "start_date");
//        $this->dropColumn("crm_staff_schedules", "finish_date");
//        $this->dropColumn("crm_staff_schedules", "work_days");
//        $this->dropColumn("crm_staff_schedules", "off_days");
//
//        $this->addColumn("crm_staff_schedules", "day_id", Schema::TYPE_INTEGER . " NOT NULL");
//        $this->addColumn("crm_staff_schedules", "is_holiday", Schema::TYPE_INTEGER . " NOT NULL");
//
//        $this->createIndex("uq_staff_schedules_day_id_staff_id", "crm_staff_schedules", ["day_id", "staff_id"]);
    }

    public function down()
    {
    }
}
