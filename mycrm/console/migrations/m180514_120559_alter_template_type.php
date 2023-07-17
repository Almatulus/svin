<?php

use yii\db\Migration;

/**
 * Class m180514_120559_alter_template_type
 */
class m180514_120559_alter_template_type extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn('{{%customer_request_templates}}', 'template', $this->text());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->alterColumn('{{%customer_request_templates}}', 'template', $this->string());
    }
}
