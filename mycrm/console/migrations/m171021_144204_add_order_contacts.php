<?php

use yii\db\Migration;

class m171021_144204_add_order_contacts extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%order_contacts_map}}', [
            'order_id'            => $this->integer()->unsigned()->notNull(),
            'company_customer_id' => $this->integer()->unsigned()->notNull(),
        ]);

        $this->addForeignKey('fk_order_contacts_map_order',
            '{{%order_contacts_map}}',
            'order_id',
            '{{%orders}}',
            'id'
        );
        $this->addForeignKey('fk_order_contacts_map_company_customer',
            '{{%order_contacts_map}}',
            'company_customer_id',
            '{{%company_customers}}',
            'id'
        );
        $this->createIndex(
            'uq_order_contacts_map_order_company_customer',
            '{{%order_contacts_map}}',
            ['order_id', 'company_customer_id'],
            true
        );
    }

    public function safeDown()
    {
        $this->dropTable('{{%order_contacts_map}}');
    }
}
