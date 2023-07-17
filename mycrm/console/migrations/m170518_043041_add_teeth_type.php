<?php

use yii\db\Migration;

class m170518_043041_add_teeth_type extends Migration
{

    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
        $this->addColumn('{{%order_tooth}}', 'type', $this->integer()->notNull()->defaultValue(1));
    }

    public function safeDown()
    {
        $this->dropColumn('{{%order_tooth}}', 'type');
    }

}
