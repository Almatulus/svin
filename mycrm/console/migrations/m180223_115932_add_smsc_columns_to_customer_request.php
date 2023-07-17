<?php

use yii\db\Migration;

/**
 * Class m180223_115932_add_smsc_columns_to_customer_request
 */
class m180223_115932_add_smsc_columns_to_customer_request extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('{{%customer_requests}}', 'smsc_id', $this->integer());
        $this->addColumn('{{%customer_requests}}', 'smsc_count', $this->integer());
        $this->addColumn('{{%customer_requests}}', 'smsc_cost', $this->double());
        $this->addColumn('{{%customer_requests}}', 'smsc_error_code', $this->integer());
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn('{{%customer_requests}}', 'smsc_id');
        $this->dropColumn('{{%customer_requests}}', 'smsc_count');
        $this->dropColumn('{{%customer_requests}}', 'smsc_cost');
        $this->dropColumn('{{%customer_requests}}', 'smsc_error_code');
    }
}
