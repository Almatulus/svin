<?php

use yii\db\Migration;

class m171020_121320_set_phone_not_required extends Migration
{
    public function safeUp()
    {
        $sql = <<<SQL
ALTER TABLE {{%customers}} ALTER COLUMN phone DROP NOT NULL;
SQL;
        $this->execute($sql);
    }

    public function safeDown()
    {
        $this->update('{{%customers}}', ['phone' => md5(rand())], ['phone' => null]);
        $sql = <<<SQL
ALTER TABLE {{%customers}} ALTER COLUMN phone SET NOT NULL;
SQL;
        $this->execute($sql);
    }
}
