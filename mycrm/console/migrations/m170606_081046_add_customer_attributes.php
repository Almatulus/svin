<?php

use yii\db\Migration;

class m170606_081046_add_customer_attributes extends Migration
{
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
        $this->addColumn("{{%customers}}", "iin", $this->string()->unique());
        $this->addColumn("{{%customers}}", "id_card_number", $this->string()->unique());

        $this->addColumn("{{%company_customers}}", "job", $this->string());
        $this->addColumn("{{%company_customers}}", "employer", $this->string());

        $this->createTable('{{%company_customer_phones}}', [
            'company_customer_id' => $this->integer(),
            'phone' => $this->string()
        ]);

        $this->addPrimaryKey('company_customer_phones_pk', '{{%company_customer_phones}}', ['company_customer_id', 'phone']);

        $this->addForeignKey('fk_phone_company_customer', '{{%company_customer_phones}}', 'company_customer_id', '{{%company_customers}}', 'id');

        $this->createIndex('company_customers_company_customer_id', '{{%company_customer_phones}}', 'company_customer_id');
        $this->createIndex('company_customers_phone', '{{%company_customer_phones}}', 'phone');
        $this->createIndex('uq_company_customers', '{{%company_customer_phones}}', ['company_customer_id', 'phone'], true);
    }

    public function safeDown()
    {
        $this->dropTable('{{%company_customer_phones}}');

        $this->dropColumn("{{%company_customers}}", "job");
        $this->dropColumn("{{%company_customers}}", "employer");

        $this->dropColumn("{{%customers}}", "iin");
        $this->dropColumn("{{%customers}}", "id_card_number");
    }
}
