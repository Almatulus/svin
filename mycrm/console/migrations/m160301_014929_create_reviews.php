<?php

use yii\db\Migration;

class m160301_014929_create_reviews extends Migration
{
    public function up()
    {
        $this->createTable('crm_reviews', [
            'id' => $this->primaryKey(),
            'customer_id' => $this->integer()->notNull(),
            'staff_id' => $this->integer()->notNull(),
            'created_time' => $this->dateTime()->notNull()->defaultValue('now()'),
            'value' => $this->integer()->notNull()->defaultValue(0),
            'comment' => $this->text(),
            'status' => $this->integer()->notNull()->defaultValue(0),
        ]);

        $this->addForeignKey('fk_reviews_customer', 'crm_reviews', 'customer_id', 'crm_customers', 'id');
        $this->addForeignKey('fk_reviews_staff', 'crm_reviews', 'staff_id', 'crm_staffs', 'id');
    }

    public function down()
    {
        $this->dropTable('crm_reviews');
    }
}
