<?php

use yii\db\Migration;

/**
 * Class m180502_065754_add_medical_record_id_to_customer
 */
class m180502_065754_add_medical_record_id_to_customer extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%company_customers}}', 'medical_record_id', $this->string());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%company_customers}}', 'medical_record_id');
    }
}
