<?php

use yii\db\Migration;

class m170112_055611_add_last_payment_to_company extends Migration
{
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
        $this->addColumn('{{%companies}}', 'last_payment', $this->date());
    }

    public function safeDown()
    {
        $this->dropColumn('{{%companies}}', 'last_payment');
    }
}
