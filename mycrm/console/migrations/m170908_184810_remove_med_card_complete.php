<?php

use yii\db\Migration;

class m170908_184810_remove_med_card_complete extends Migration
{
    public function safeUp()
    {
        $this->dropColumn('{{%med_cards}}', 'completed_at');
    }

    public function safeDown()
    {
        $this->addColumn(
            '{{%med_cards}}',
            'completed_at',
            $this->dateTime()->defaultValue(null)
        );
    }
}
