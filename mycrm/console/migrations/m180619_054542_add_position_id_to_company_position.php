<?php

use core\models\Position;
use yii\db\Migration;

/**
 * Class m180619_054542_add_position_id_to_company_position
 */
class m180619_054542_add_position_id_to_company_position extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn("{{%company_positions}}", "position_id", $this->integer()->unsigned());
        $this->addForeignKey('fk_company_position_position', "{{%company_positions}}", 'position_id', Position::tableName(), 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey("fk_company_position_position","{{%company_positions}}");
        $this->dropColumn("{{%company_positions}}", "position_id");
    }
}
