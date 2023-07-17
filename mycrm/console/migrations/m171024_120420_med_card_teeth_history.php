<?php

use yii\db\Migration;

class m171024_120420_med_card_teeth_history extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%med_card_teeth_history}}', [
            'id' => $this->primaryKey(),
            'teeth_num' => $this->integer()->unsigned()->notNull(),
            'type' => $this->integer()->unsigned()->notNull(),
            'mobility' => $this->integer()->unsigned(),
            'med_card_tab_id' => $this->integer()->unsigned()->notNull(),
            'diagnosis_id' => $this->integer()->unsigned()->notNull(),
            'diagnosis_name' => $this->string(),
            'diagnosis_abbreviation' => $this->string(),
            'diagnosis_color' => $this->string(),
            'created_user_id' => $this->integer(),
            'created_time' => $this->dateTime()->notNull()->defaultExpression('NOW()'),
        ]);

        $this->addForeignKey(
            'fk_med_card_teeth_history_created_user',
            '{{%med_card_teeth_history}}',
            'created_user_id',
            '{{%users}}',
            'id'
        );
    }

    public function safeDown()
    {
        $this->dropTable('{{%med_card_teeth_history}}');
    }
}
