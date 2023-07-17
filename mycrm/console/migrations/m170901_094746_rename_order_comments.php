<?php

use yii\db\Migration;

class m170901_094746_rename_order_comments extends Migration
{
    public function safeUp()
    {
        $this->renameTable(
            '{{%order_comments}}',
            '{{%med_card_tab_comments}}'
        );
        $this->dropColumn('{{%med_card_tab_comments}}', 'order_id');
    }

    public function safeDown()
    {
        $this->renameTable(
            '{{%med_card_tab_comments}}',
            '{{%order_comments}}'
        );
    }
}
