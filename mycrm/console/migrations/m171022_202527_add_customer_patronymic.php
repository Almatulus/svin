<?php

use yii\db\Migration;

class m171022_202527_add_customer_patronymic extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%customers}}', 'patronymic', $this->string());
    }

    public function safeDown()
    {
        $this->dropColumn('{{%customers}}', 'patronymic');
    }
}
