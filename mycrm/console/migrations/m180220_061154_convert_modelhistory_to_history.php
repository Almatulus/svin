<?php

use yii\db\Expression;
use yii\db\Migration;

/**
 * Class m180220_061154_convert_modelhistory_to_history
 */
class m180220_061154_convert_modelhistory_to_history extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $query = new \yii\db\Query();
        $query->select(['date', 'table', 'field_id', 'type', 'user_id', new Expression("COUNT(*)")])
            ->groupBy(['date', 'table', 'field_id', 'type', 'user_id'])
            ->from('{{%modelhistory}}');

        $i = 0;
        foreach ($query->each() as $row) {

            $query2 = new \yii\db\Query();
            $attributes = $query2->select(['field_name', 'old_value', 'new_value'])
                ->from('{{%modelhistory}}')
                ->where([
                    'date' => $row['date'],
                    'table' => $row['table'],
                    'field_id' => $row['field_id'],
                    'type' => $row['type'],
                    'user_id' => $row['user_id'],
                ])
                ->all();

            $initiator = $row['user_id'];
            $event = $this->typeToEventLabels()[$row['type']];
            $table_name = $row['table'];
            $row_id = $row['field_id'];
            $created_time = $row['date'];

            $log = [];

            if($row['type'] != 0) {
                foreach ($attributes as $attribute) {
                    $field_name = $attribute['field_name'];
                    $old_value = $attribute['old_value'];
                    $new_value = $attribute['new_value'];

                    $log['old'][$field_name] = $old_value;
                    $log['new'][$field_name] = $new_value;
                }
            }

            $log = array_filter($log);
            $log = json_encode($log);

            // ******************

            $connection = Yii::$app->db;
            $insert = $connection->createCommand()->insert('{{%history}}', [
                'initiator' => $initiator,
                'ip' => null,
                'event' => $event,
                'class' => null,
                'table_name' => $table_name,
                'row_id' => $row_id,
                'log' => $log,
                'created_time' => $created_time,
            ])->execute();

            echo $i++ . " ";
        }

        return true;
    }

    private function typeToEventLabels()
    {
        return [
            0 => 'afterInsert',
            1 => 'afterUpdate',
            2 => 'afterDelete',
        ];
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $connection = Yii::$app->db;
        $connection->createCommand()->delete('{{%history}}', [])->execute();
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180220_061154_convert_modelhistory_to_history cannot be reverted.\n";

        return false;
    }
    */
}
