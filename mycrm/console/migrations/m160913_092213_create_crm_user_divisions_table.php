<?php

use yii\db\Migration;

/**
 * Handles the creation for table `crm_user_divisions`.
 */
class m160913_092213_create_crm_user_divisions_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('crm_user_divisions', [
            'id' => $this->primaryKey(),
            'staff_id' => $this->integer()->notNull(),
            'division_id' => $this->integer()->notNull()
        ]);

        $this->addForeignKey('fk_staff', 'crm_user_divisions', 'staff_id', 'crm_staffs', 'id');
        $this->addForeignKey('fk_division', 'crm_user_divisions', 'division_id', 'crm_divisions', 'id');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropForeignKey('fk_staff', 'crm_user_divisions');
        $this->dropForeignKey('fk_division', 'crm_user_divisions');
        $this->dropTable('crm_user_divisions');
    }
}
