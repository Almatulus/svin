<?php

use yii\db\Migration;

/**
 * Handles the dropping of table `historymodel`.
 */
class m180622_065855_drop_historymodel_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableSchema = Yii::$app->db->schema->getTableSchema('{{%modelhistory}}');
        if ($tableSchema !== null) {
            $this->dropTable('{{%modelhistory}}');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {

    }
}
