<?php

use yii\db\Migration;

/**
 * Class m180525_063629_add_1c_code
 */
class m180525_063629_add_1c_code extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%staffs}}', 'code_1c', $this->string());
        $this->addColumn('{{%division_services}}', 'code_1c', $this->string());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {

        $this->dropColumn('{{%staffs}}', 'code_1c');
        $this->dropColumn('{{%division_services}}', 'code_1c');
    }
}
