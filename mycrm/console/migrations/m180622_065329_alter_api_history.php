<?php

use yii\db\Migration;

/**
 * Class m180622_065329_alter_api_history
 */
class m180622_065329_alter_api_history extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn(\core\models\ApiHistory::tableName(), 'request_method', $this->string());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn(\core\models\ApiHistory::tableName(), 'request_method');
    }
}
