<?php

use yii\db\Migration;

class m170325_202018_remove_order_comments extends Migration
{
    public function safeUp()
    {
        $this->dropColumn('{{%orders}}', 'comments');
        $this->dropColumn('{{%orders}}', 'discount');
    }

    public function safeDown()
    {
        $this->addColumn('{{%orders}}', 'comments', $this->text());
        $this->addColumn('{{%orders}}', 'discount', $this->integer()->notNull()->defaultValue(0));
    }
}
