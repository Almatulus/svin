<?php

use yii\db\Migration;

class m171020_113445_fix_default_timestamp extends Migration
{
    public function safeUp()
    {
        $sql = <<<SQL
ALTER TABLE {{%staff_reviews}} ALTER created_time SET DEFAULT NOW(); 
SQL;
        $this->execute($sql);

        $sql = <<<SQL
ALTER TABLE {{%division_reviews}} ALTER created_time SET DEFAULT NOW(); 
SQL;
        $this->execute($sql);

        $sql = <<<SQL
ALTER TABLE {{%customer_requests}} ALTER created_time SET DEFAULT NOW(); 
SQL;
        $this->execute($sql);

        $sql = <<<SQL
ALTER TABLE {{%api_history}} ALTER created_time SET DEFAULT NOW(); 
SQL;
        $this->execute($sql);
    }

    public function safeDown()
    {

    }
}
