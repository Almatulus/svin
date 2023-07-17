<?php

use yii\db\Migration;

/**
 * Class m180416_060145_add_customer_request_id_column_to_company_payment_log
 */
class m180416_060145_add_customer_request_id_column_to_company_payment_log extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%company_payment_log}}', 'customer_request_id', $this->integer());

        $this->addForeignKey('fk_customer_request_payment_log', '{{%company_payment_log}}',
            'customer_request_id', '{{%customer_requests}}', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk_customer_request_payment_log', '{{%company_payment_log}}');
        $this->dropColumn('{{%company_payment_log}}', 'customer_request_id');
    }
}
