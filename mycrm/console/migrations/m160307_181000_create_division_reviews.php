<?php

use yii\db\Migration;

class m160307_181000_create_division_reviews extends Migration
{
    public function up()
    {
        $this->createTable('crm_division_reviews', [
            'id' => $this->primaryKey(),
            'customer_id' => $this->integer()->notNull(),
            'division_id' => $this->integer()->notNull(),
            'created_time' => $this->dateTime()->notNull()->defaultValue('now()'),
            'value' => $this->integer()->notNull()->defaultValue(0),
            'comment' => $this->text(),
            'status' => $this->integer()->notNull()->defaultValue(0),
        ]);

        $this->addForeignKey('fk_division_reviews_customer', 'crm_division_reviews', 'customer_id', 'crm_customers', 'id');
        $this->addForeignKey('fk_division_reviews_division', 'crm_division_reviews', 'division_id', 'crm_divisions', 'id');
    }

    public function down()
    {
        $this->dropTable('crm_division_reviews');
    }
}
