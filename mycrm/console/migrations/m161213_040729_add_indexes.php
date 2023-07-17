<?php

use yii\db\Migration;

class m161213_040729_add_indexes extends Migration
{
    // public function up()
    // {

    // }

    // public function down()
    // {
    //     echo "m161213_040729_add_indexes cannot be reverted.\n";

    //     return false;
    // }

    
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
        $this->createIndex('phone_idx', '{{%customers}}', 'phone');
        $this->createIndex('name_idx', '{{%customers}}', 'name');
        $this->createIndex('lastname_idx', '{{%customers}}', 'lastname');

        $this->createIndex('company_customer_id_idx', '{{%company_customer_category_map}}', 'company_customer_id');

        $this->createIndex('order_id_idx', '{{%staff_schedules}}', 'order_id');          
    }

    public function safeDown()
    {
        $this->dropIndex('phone_idx', '{{%customers}}');
        $this->dropIndex('name_idx', '{{%customers}}');
        $this->dropIndex('lastname_idx', '{{%customers}}');

        $this->dropIndex('company_customer_id_idx', '{{%company_customer_category_map}}');

        $this->dropIndex('order_id_idx', '{{%staff_schedules}}');     
    }
    
}
