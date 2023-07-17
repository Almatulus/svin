<?php

use yii\db\Migration;

/**
 * Class m171226_060705_company_show_new_interface
 */
class m171226_060705_company_show_new_interface extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('{{%companies}}', 'show_new_interface', $this->boolean()->notNull()->defaultValue(false));
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn('{{%companies}}', 'show_new_interface');
    }
}
