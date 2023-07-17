<?php

use yii\db\Migration;

/**
 * Class m180413_062451_add_smsc_status_to_customer_request
 */
class m180413_062451_add_smsc_status_to_customer_request extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('{{%customer_requests}}', 'smsc_status', $this->integer());
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn('{{%customer_requests}}', 'smsc_status');
    }
}
