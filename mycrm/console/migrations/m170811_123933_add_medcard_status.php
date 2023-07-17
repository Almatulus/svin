<?php

use yii\db\Migration;

class m170811_123933_add_medcard_status extends Migration
{
    public function safeUp()
    {
        $this->addColumn(
            '{{%med_cards}}',
            'completed_at',
            $this->dateTime()->defaultValue(null)
        );
    }

    public function safeDown()
    {
        $this->dropColumn(
            '{{%med_cards}}',
            'completed_at'
        );
    }
}
