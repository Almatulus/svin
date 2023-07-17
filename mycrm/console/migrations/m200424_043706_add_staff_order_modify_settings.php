<?php

use yii\db\Migration;

/**
 * Class m200424_043706_add_staff_order_modify_settings
 */
class m200424_043706_add_staff_order_modify_settings extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn(\core\models\Staff::tableName(), 'can_update_order', $this->boolean()->defaultValue(false));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn(\core\models\Staff::tableName(), 'can_update_order');
    }
}
