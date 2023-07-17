<?php

use yii\db\Migration;

class m170220_183215_division_rename_columns extends Migration
{
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
        $this->renameColumn('{{%divisions}}', 'position_lat', 'latitude');
        $this->renameColumn('{{%divisions}}', 'position_lon', 'longitude');
    }

    public function safeDown()
    {
        $this->renameColumn('{{%divisions}}', 'latitude', 'position_lat');
        $this->renameColumn('{{%divisions}}', 'longitude', 'position_lon');
    }
}
