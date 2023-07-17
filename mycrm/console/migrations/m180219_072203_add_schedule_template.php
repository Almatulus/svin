<?php

use yii\db\Migration;

/**
 * Class m180219_072203_add_schedule_template
 */
class m180219_072203_add_schedule_template extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTable('{{%schedule_templates}}', [
            'id'            => $this->primaryKey(),
            'staff_id'      => $this->integer()->unsigned()->notNull(),
            'division_id'   => $this->integer()->unsigned()->notNull(),
            'interval_type' => $this->integer()->unsigned()->notNull(),
            'type'          => $this->integer()->unsigned()->notNull(),
            'created_at'    => $this->dateTime()->notNull(),
            'updated_at'    => $this->dateTime()->notNull(),
            'created_by'    => $this->integer()->unsigned()->notNull(),
            'updated_by'    => $this->integer()->unsigned()->notNull()
        ]);

        $this->addForeignKey('fk_schedule_templates_staff', '{{%schedule_templates}}', 'staff_id',
            '{{%staffs}}', 'id');
        $this->addForeignKey('fk_schedule_templates_division', '{{%schedule_templates}}', 'division_id',
            '{{%divisions}}', 'id');
        $this->addForeignKey('fk_schedule_templates_creator', '{{%schedule_templates}}', 'created_by',
            '{{%users}}', 'id');
        $this->addForeignKey('fk_schedule_templates_updater', '{{%schedule_templates}}', 'updated_by',
            '{{%users}}', 'id');

        $this->createTable('{{%schedule_template_intervals}}', [
            'schedule_template_id' => $this->integer()->notNull(),
            'day'                  => $this->integer()->notNull(),
            'start'                => $this->time(),
            'end'                  => $this->time(),
            'break_start'          => $this->time(),
            'break_end'            => $this->time(),
        ]);
        $this->addPrimaryKey($this->db->tablePrefix . "schedules_template_intervals_pkey",
            '{{%schedule_template_intervals}}', ['schedule_template_id', 'day']
        );
        $this->addForeignKey('fk_schedule_template_interval_template', '{{%schedule_template_intervals}}',
            'schedule_template_id', '{{%schedule_templates}}', 'id');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTable('{{%schedule_template_intervals}}');
        $this->dropTable('{{%schedule_templates}}');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180219_072203_add_schedule_template cannot be reverted.\n";

        return false;
    }
    */
}
