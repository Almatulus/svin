<?php

use core\models\Payment;
use yii\db\Migration;

class m170806_112151_add_new_payment extends Migration
{
    public function safeUp()
    {
        $model = new Payment();
        $model->name = 'insurance';
        $model->status = Payment::STATUS_ENABLED;
        $model->insert(false);
    }

    public function safeDown()
    {

    }
}
