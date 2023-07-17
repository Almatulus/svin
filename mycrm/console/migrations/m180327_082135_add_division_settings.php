<?php

use yii\db\Migration;

/**
 * Class m180327_082135_add_division_settings
 */
class m180327_082135_add_division_settings extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTable('{{%division_settings}}', [
            'id'                             => $this->primaryKey(),
            'division_id'                    => $this->integer()->unsigned()->notNull()->unique(),
            'notification_time_before_lunch' => $this->time(),
            'notification_time_after_lunch'  => $this->time(),
        ]);

        $this->addForeignKey('fk_division_settings_division', '{{%division_settings}}', 'division_id',
            '{{%divisions}}', 'id');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTable('{{%division_settings}}');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180327_082135_add_division_settings cannot be reverted.\n";

        return false;
    }
    */
}
