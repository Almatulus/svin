<?php

use yii\db\Migration;

class m170706_100044_add_tooth_diagnosis extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%order_tooth}}', 'diagnosis_id', $this->integer()->unsigned());
        $this->addColumn('{{%order_tooth}}', 'mobility', $this->integer()->unsigned());
    }

    public function safeDown()
    {
        $this->addColumn('{{%order_tooth}}', 'diagnosis_id');
        $this->addColumn('{{%order_tooth}}', 'mobility');
    }
}
