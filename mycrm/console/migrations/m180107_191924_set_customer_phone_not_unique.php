<?php

use yii\db\Migration;

/**
 * Class m180107_191924_set_customer_phone_not_unique
 */
class m180107_191924_set_customer_phone_not_unique extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->dropIndex('uq_customer_phone', '{{%customers}}');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->createIndex('uq_customer_phone', '{{%customers}}', 'phone', true);
    }
}
