<?php

use yii\db\Migration;

/**
 * Class m171107_044540_set_staff_division_service_map
 */
class m171107_044540_set_staff_division_service_map extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->renameTable('{{%staff_division_services}}', '{{%staff_division_service_map}}');
        $this->dropColumn('{{%staff_division_service_map}}', 'id');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->addColumn('{{%staff_division_service_map}}', 'id', $this->primaryKey());
        $this->renameTable('{{%staff_division_service_map}}', '{{%staff_division_services}}');
    }
}
