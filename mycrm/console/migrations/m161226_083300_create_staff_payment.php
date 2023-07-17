<?php

use yii\db\Migration;

class m161226_083300_create_staff_payment extends Migration
{
    // public function up()
    // {
        
    // }

    // public function down()
    // {
    //     echo "m161226_083300_create_staff_payment cannot be reverted.\n";

    //     return false;
    // }

    
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
        $this->createTable('{{%staff_payments}}', [
            'id' => $this->primaryKey(),
            'start_date' => $this->date(),
            'end_date' => $this->date(),
            'staff_id' => $this->integer(),
            'salary' => $this->double()->notNull(),
            'created_at' => $this->dateTime(),
            'updated_at' => $this->dateTime(),
        ]);

        $this->createIndex('start_date_ind', '{{%staff_payments}}', 'start_date');
        $this->createIndex('end_date_idx', '{{%staff_payments}}', 'end_date');
        $this->createIndex('staff_id_idx', '{{%staff_payments}}', 'staff_id');
        $this->createIndex('salary_idx', '{{%staff_payments}}', 'salary');
        $this->createIndex('created_at_idx', '{{%staff_payments}}', 'created_at');

        $this->addForeignKey('fk_staff_payment', '{{%staff_payments}}', 'staff_id', '{{%staffs}}', 'id');

        // $this->createTable('{{%staff_payment_motivations}}', [
        //     'id' => $this->primaryKey(),
        //     'order_id' => $this->integer()->notNull(),
        //     'payment_id' => $this->integer()->notNull(),
        //     'value' => $this->integer()
        // ]);
    }

    public function safeDown()
    {
        $this->dropTable("{{%staff_payments}}");
    }
    
}
