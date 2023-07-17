<?php

use core\models\ApiHistory;
use yii\db\Migration;

/**
 * Class m180313_081443_add_api_history_attributes
 */
class m180313_081443_add_api_history_attributes extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn(ApiHistory::tableName(), 'request_body', $this->text());
        $this->addColumn(ApiHistory::tableName(), 'request_query', $this->text());
        $this->addColumn(ApiHistory::tableName(), 'user_id', $this->integer());
        $this->addColumn(ApiHistory::tableName(), 'running_time', $this->float());
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn(ApiHistory::tableName(), 'request_body');
        $this->dropColumn(ApiHistory::tableName(), 'request_query');
        $this->dropColumn(ApiHistory::tableName(), 'user_id');
        $this->dropColumn(ApiHistory::tableName(), 'running_time');
    }
}
