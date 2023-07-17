<?php

use yii\db\Migration;

class m170118_061443_add_division_schedule extends Migration
{
    /*public function up()
    {

    }

    public function down()
    {
        echo "m170118_061443_add_division_schedule cannot be reverted.\n";

        return false;
    }*/

    
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
        $this->createTable('{{%division_schedule}}', [
            'id' => $this->primaryKey(),
            'division_id' => $this->integer()->unsigned()->notNull()->comment("Подразделение"),
            'day_num' => $this->smallInteger()->unsigned()->notNull()->comment("День недели"),
            'is_open' => $this->boolean()->defaultValue(true)->notNull()->comment("Открыто"),
            'from' => $this->time()->notNull()->comment("С"),
            'to' => $this->time()->notNull()->comment("До"),
        ]);

        $this->addForeignKey('fk_schedule_division', '{{%division_schedule}}', 'division_id', '{{%divisions}}', 'id');

        $this->createIndex('division_schedules_division_id_idx', '{{%division_schedule}}', 'division_id');
        $this->createIndex('division_schedules_day_num_idx', '{{%division_schedule}}', 'day_num');
        $this->createIndex('division_schedules_from_idx', '{{%division_schedule}}', 'from');
        $this->createIndex('division_schedules_to_idx', '{{%division_schedule}}', 'to');
    }

    public function safeDown()
    {
        $this->dropTable('{{%division_schedule}}');
    }
    
}
