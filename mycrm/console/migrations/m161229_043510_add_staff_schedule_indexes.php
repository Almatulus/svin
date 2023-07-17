<?php

use yii\db\Migration;

class m161229_043510_add_staff_schedule_indexes extends Migration
{
    /*public function up()
    {

    }

    public function down()
    {
        echo "m161229_043510_add_staff_schedule_indexes cannot be reverted.\n";

        return false;
    }*/

    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
        $this->createIndex('staff_division_id_idx', '{{%staffs}}', 'division_id');
        $this->createIndex('staff_company_position_id_idx', '{{%staffs}}', 'company_position_id');
        $this->createIndex('staff_status_idx', '{{%staffs}}', 'status');

        $this->createIndex('order_company_customer_id_idx', '{{%orders}}', 'company_customer_id');
        $this->createIndex('order_datetime_idx', '{{%orders}}', 'datetime');
        $this->createIndex('order_status_idx', '{{%orders}}', 'status');

        $this->createIndex('schedule_datetime_idx', '{{%staff_schedules}}', 'datetime');
        $this->createIndex('schedule_staff_id_idx', '{{%staff_schedules}}', 'staff_id');          
    }

    public function safeDown()
    {
        $this->dropIndex('staff_division_id_idx', '{{%staffs}}');
        $this->dropIndex('staff_company_position_id_idx', '{{%staffs}}');
        $this->dropIndex('staff_status_idx', '{{%staffs}}');

        $this->dropIndex('order_company_customer_id_idx', '{{%orders}}');
        $this->dropIndex('order_datetime_idx', '{{%orders}}');
        $this->dropIndex('order_status_idx', '{{%orders}}');

        $this->dropIndex('schedule_datetime_idx', '{{%staff_schedules}}');
        $this->dropIndex('schedule_staff_id_idx', '{{%staff_schedules}}');      
    }
}
