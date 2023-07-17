<?php

use yii\db\Migration;

/**
 * Class m180119_044753_add_division_default_notification_time
 */
class m180119_044753_add_division_default_notification_time extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn(
            '{{%divisions}}',
            'default_notification_time',
            $this->integer()->unsigned()->notNull()->defaultValue(0)
        );
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn('{{%divisions}}', 'default_notification_time');
    }
}
