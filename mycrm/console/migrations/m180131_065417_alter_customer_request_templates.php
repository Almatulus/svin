<?php

use core\helpers\customer\RequestTemplateHelper;
use yii\db\Migration;

/**
 * Class m180131_065417_alter_customer_request_templates
 */
class m180131_065417_alter_customer_request_templates extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('{{%customer_request_templates}}', 'type',
            $this->integer()->unsigned()->defaultValue(RequestTemplateHelper::TYPE_DEFAULT)->notNull());
        $this->addColumn('{{%customer_request_templates}}', 'quantity', $this->integer()->unsigned());
        $this->addColumn('{{%customer_request_templates}}', 'quantity_type', $this->integer()->unsigned());
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn('{{%customer_request_templates}}', 'type');
        $this->dropColumn('{{%customer_request_templates}}', 'quantity');
        $this->dropColumn('{{%customer_request_templates}}', 'quantity_type');
    }
}
