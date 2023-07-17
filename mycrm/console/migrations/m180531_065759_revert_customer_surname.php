<?php

use core\models\customer\Customer;
use yii\db\Migration;

/**
 * Class m180531_065759_revert_customer_surname
 */
class m180531_065759_revert_customer_surname extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $query = \core\models\HistoryEntity::find()
            ->where(['>', 'created_time', '2018-05-30 00:00:00'])
            ->andWhere([
                'table_name' => '{{%customers}}',
                'event' => 'afterUpdate'
            ]);

        $fixedCount = 0;
        foreach ($query->each() as $history){
            $log = $history->log;

            if(isset($log['new']['lastname']) && $log['new']['lastname'] == null){
                $this->update(Customer::tableName(), ['lastname' => $log['old']['lastname']], ['id' => $history->row_id]);
                echo "CHANGED " . $log['new']['lastname']. " TO " . $log['old']['lastname'] . "\n";
                $fixedCount++;
            }

            if(isset($log['new']['patronymic']) && $log['new']['patronymic'] == null){
                $this->update(Customer::tableName(), ['patronymic' => $log['old']['patronymic']], ['id' => $history->row_id]);
                echo "CHANGED " . $log['new']['patronymic']. " TO " . $log['old']['patronymic'] . "\n";
                $fixedCount++;
            }
        }

        echo "\nFIXED COUNT: {$fixedCount}\n";
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {

    }
}
