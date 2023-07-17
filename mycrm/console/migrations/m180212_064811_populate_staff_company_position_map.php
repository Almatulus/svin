<?php

use yii\db\Migration;

/**
 * Class m180212_064811_populate_staff_company_position_map
 */
class m180212_064811_populate_staff_company_position_map extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        echo "Populate Staff_company_position_map\n";

        $offset = 0;
        $limit = 10;

        $total = 0;
        do {
            $query = new \yii\db\Query();
            $rows = $query->select(['id', 'company_position_id'])
                ->from('{{%staffs}}')
                ->andWhere(['IS NOT', 'company_position_id', null])
                ->limit($limit)
                ->offset($offset)
                ->all();

            foreach ($rows as $row) {
                $connection = Yii::$app->db;
                $affected = $connection->createCommand()->insert('{{%staff_company_position_map}}', [
                    'staff_id' => $row['id'],
                    'company_position_id' => $row['company_position_id'],
                ])->execute();
                echo "staff_id[" . $row['id'] . "] company_position_id[" . $row['company_position_id'] . "]\n";
            }

            $total += sizeof($rows);
            $offset += $limit;
        } while (sizeof($rows) > 0);

        echo "Added {$total}\n";
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $connection = Yii::$app->db;
        $connection->createCommand()->delete('{{%staff_company_position_map}}')->execute();
    }
}
