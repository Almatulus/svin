<?php

use core\helpers\GenderHelper;
use core\models\customer\Customer;
use yii\db\Migration;

class m170920_073852_fix_customer_gender extends Migration
{
    public function safeUp()
    {
        Customer::updateAll(['gender' => GenderHelper::GENDER_UNDEFINED], ['gender' => 0]);
    }

    public function safeDown()
    {
    }
}
