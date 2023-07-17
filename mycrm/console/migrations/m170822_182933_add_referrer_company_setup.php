<?php

use yii\db\Migration;

class m170822_182933_add_referrer_company_setup extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%companies}}', 'show_referrer', $this->boolean()->notNull()->defaultValue(false));
    }

    public function safeDown()
    {
        $this->dropColumn('{{%companies}}', 'show_referrer');
    }
}
