<?php

use yii\db\Migration;

/**
 * Handles the creation of table `staff_company_positions_map`.
 */
class m180212_063442_create_staff_company_positions_map_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('{{%staff_company_position_map}}', [
            'staff_id' => $this->integer()->notNull(),
            'company_position_id' => $this->integer()->notNull(),
            'PRIMARY KEY (staff_id, company_position_id)',
        ]);

        $this->addForeignKey('fk_staff_position_map_2_staff', '{{%staff_company_position_map}}', 'staff_id', '{{%staffs}}', 'id');
        $this->addForeignKey('fk_staff_position_map_2_company_position', '{{%staff_company_position_map}}', 'company_position_id', '{{%company_positions}}', 'id');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropForeignKey('fk_staff_position_map_2_company_position', '{{%staff_company_position_map}}');
        $this->dropForeignKey('fk_staff_position_map_2_staff', '{{%staff_company_position_map}}');

        $this->dropTable('{{%staff_company_position_map}}');
    }
}
