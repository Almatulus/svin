<?php

use yii\db\Migration;

/**
 * Class m180411_042658_add_payment_date_column_to_staff_payments
 */
class m180411_042658_add_payment_date_column_to_staff_payments extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('{{%staff_payments}}', 'payment_date', $this->date());

        $this->execute("
            UPDATE {{%staff_payments}}
            SET payment_date=created_at
        ");
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn('{{%staff_payments}}', 'payment_date');
    }
}
