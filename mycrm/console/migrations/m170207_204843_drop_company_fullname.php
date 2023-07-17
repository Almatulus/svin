<?php

use yii\db\Migration;

class m170207_204843_drop_company_fullname extends Migration
{
    public function safeUp()
    {
        $this->dropColumn('{{%companies}}', 'fullname');
    }

    public function safeDown()
    {
        return true;
    }
}
