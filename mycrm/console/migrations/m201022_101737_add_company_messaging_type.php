<?php

use yii\db\Migration;

/**
 * Class m201022_101737_add_company_messaging_type
 */
class m201022_101737_add_company_messaging_type extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn(\core\models\company\Company::tableName(), 'messaging_type', $this->integer()->defaultValue(1));
        $this->addColumn(\core\models\company\Company::tableName(), 'chatapi_token', $this->string());
        $this->addColumn(\core\models\company\Company::tableName(), 'chatapi_url', $this->string());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn(\core\models\company\Company::tableName(), 'messaging_type');
        $this->dropColumn(\core\models\company\Company::tableName(), 'chatapi_token');
        $this->dropColumn(\core\models\company\Company::tableName(), 'chatapi_url');
    }
}
