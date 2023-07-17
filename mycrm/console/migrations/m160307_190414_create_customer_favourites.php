<?php

use yii\db\Migration;

class m160307_190414_create_customer_favourites extends Migration
{
    public function up()
    {
        $this->createTable('crm_customer_favourites', [
            'id' => $this->primaryKey(),
            'customer_id' => $this->integer()->notNull(),
            'division_id' => $this->integer()->notNull(),
        ]);

        $this->addForeignKey('fk_customer_favourites_customer', 'crm_customer_favourites', 'customer_id', 'crm_customers', 'id');
        $this->addForeignKey('fk_customer_favourites_division', 'crm_customer_favourites', 'division_id', 'crm_divisions', 'id');
        $this->createIndex("uq_customer_favourites_customer_division", "crm_customer_favourites", ["customer_id", "division_id"], true);
    }

    public function down()
    {
        $this->dropTable('crm_customer_favourites');
    }
}
