<?php

use yii\db\Migration;

class m170208_000623_add_webcalls_domain extends Migration
{
    public function safeUp()
    {
        $this->addColumn("{{%company_webcalls}}", 'domain', $this->string());
    }

    public function safeDown()
    {
        $this->dropColumn("{{%company_webcalls}}", 'domain');
    }
}
