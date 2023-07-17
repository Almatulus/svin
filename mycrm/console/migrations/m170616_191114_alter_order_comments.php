<?php

use yii\db\Migration;

class m170616_191114_alter_order_comments extends Migration
{
    public function up()
    {
        $this->alterColumn('{{%order_comments}}', 'comment', $this->text());
    }

    public function down()
    {
        
    }
}
