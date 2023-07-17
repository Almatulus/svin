<?php

use core\models\division\DivisionPayment;
use core\models\Payment;
use yii\db\Migration;

/**
 * Class m180125_053322_remove_debt_payment
 */
class m180125_053322_remove_debt_payment extends Migration
{
    private $redundant_payment_id = 7;

    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        DivisionPayment::updateAll(['status' => Payment::STATUS_DISABLED], ['payment_id' => $this->redundant_payment_id]);
        Payment::updateAll(['status' => Payment::STATUS_DISABLED], ['id' => $this->redundant_payment_id]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        DivisionPayment::updateAll(['status' => Payment::STATUS_ENABLED], ['payment_id' => $this->redundant_payment_id]);
        Payment::updateAll(['status' => Payment::STATUS_ENABLED], ['id' => $this->redundant_payment_id]);
    }
}
