<?php

use yii\db\Migration;

/**
 * Class m180228_093808_add_services_to_staff_payment
 */
class m180228_093808_add_services_to_staff_payment extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTable('{{%staff_payment_services}}', [
            'staff_payment_id' => $this->integer()->unsigned()->notNull(),
            'order_service_id' => $this->integer()->unsigned()->notNull(),
            'payroll_id'       => $this->integer()->unsigned()->notNull(),
            'percent'          => $this->integer()->unsigned()->notNull(),
            'sum'              => $this->integer()->unsigned()->notNull()
        ]);

        $this->addPrimaryKey($this->db->tablePrefix . 'staff_payment_services_pkey', '{{%staff_payment_services}}',
            ['staff_payment_id', 'order_service_id']
        );

        $this->addForeignKey('fk_staff_payment_services_staff_payment', '{{%staff_payment_services}}',
            'staff_payment_id', '{{%staff_payments}}', 'id');
        $this->addForeignKey('fk_staff_payment_services_order_service', '{{%staff_payment_services}}',
            'order_service_id', '{{%order_services}}', 'id');
        $this->addForeignKey('fk_staff_payment_services_staff_payroll', '{{%staff_payment_services}}',
            'payroll_id', '{{%payrolls}}', 'id');

        $this->createTable('{{%company_cashflow_salaries}}', [
            'staff_payment_id' => $this->integer()->unsigned()->notNull(),
            'cashflow_id'      => $this->integer()->unsigned()->notNull()
        ]);

        $this->addPrimaryKey($this->db->tablePrefix . 'company_cashflow_salaries_pkey',
            '{{%company_cashflow_salaries}}',
            ['staff_payment_id', 'cashflow_id']
        );

        $this->addForeignKey('fk_company_cashflow_salaries_staff_payment', '{{%company_cashflow_salaries}}',
            'staff_payment_id', '{{%staff_payments}}', 'id');
        $this->addForeignKey('fk_company_cashflow_salaries_cashflow', '{{%company_cashflow_salaries}}',
            'cashflow_id', '{{%company_cashflows}}', 'id');

    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTable('{{%company_cashflow_salaries}}');
        $this->dropTable('{{%staff_payment_services}}');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180228_093808_add_services_to_staff_payment cannot be reverted.\n";

        return false;
    }
    */
}
