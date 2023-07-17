<?php

use yii\db\Migration;

/**
 * Class m180605_053144_set_payment_types
 */
class m180605_053144_set_payment_types extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->update("{{%payments}}", ['type' => \core\helpers\company\PaymentHelper::CASH], ['name' => 'cash']);
        $this->update("{{%payments}}", ['type' => \core\helpers\company\PaymentHelper::CARD], ['name' => 'card']);
        $this->update("{{%payments}}", ['type' => \core\helpers\company\PaymentHelper::CERTIFICATE],
            ['name' => 'certificate']);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->update("{{%payments}}", ['type' => null], ['name' => 'cash']);
        $this->update("{{%payments}}", ['type' => null], ['name' => 'card']);
        $this->update("{{%payments}}", ['type' => null], ['name' => 'certificate']);
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180605_053144_set_payment_types cannot be reverted.\n";

        return false;
    }
    */
}
