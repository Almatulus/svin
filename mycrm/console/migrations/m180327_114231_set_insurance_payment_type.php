<?php

use core\helpers\company\PaymentHelper;
use core\models\Payment;
use yii\db\Migration;

/**
 * Class m180327_114231_set_insurance_payment_type
 */
class m180327_114231_set_insurance_payment_type extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->update(Payment::tableName(), ['type' => PaymentHelper::INSURANCE], ['id' => 8]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->update(Payment::tableName(), ['type' => null], ['id' => 8]);
    }
}
