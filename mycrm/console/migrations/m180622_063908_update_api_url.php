<?php

use yii\db\Migration;

/**
 * Class m180622_063908_update_api_url
 */
class m180622_063908_update_api_url extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn(\core\models\ApiHistory::tableName(), 'url', $this->text());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->alterColumn(\core\models\ApiHistory::tableName(), 'url', $this->string());
    }
}
