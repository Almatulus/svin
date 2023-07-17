<?php

use yii\db\Migration;

class m170829_162845_create_tooth_diagnoses extends Migration
{
    public function safeUp()
    {
        $this->dropColumn('{{%order_tooth}}', 'order_id');
        $this->renameTable('{{%order_tooth}}', '{{%med_card_tooth}}');

        $this->createTable('{{%med_card_teeth_diagnoses}}', [
            'id'           => $this->primaryKey(),
            'company_id'   => $this->integer()->unsigned()->notNull(),
            'name'         => $this->string()->notNull(),
            'abbreviation' => $this->string()->notNull(),
            'color'        => $this->string()->notNull(),
        ]);

        $this->addForeignKey(
            'fk_med_card_teeth_diagnoses_company',
            '{{%med_card_teeth_diagnoses}}',
            'company_id',
            '{{%companies}}',
            'id'
        );
    }

    public function safeDown()
    {
        $this->dropTable('{{%med_card_teeth_diagnoses}}');

        $this->renameTable('{{%med_card_tooth}}', '{{%order_tooth}}');
        $this->addColumn(
            '{{%order_tooth}}',
            'order_id',
            $this->integer()->unsigned()
        );
    }
}
