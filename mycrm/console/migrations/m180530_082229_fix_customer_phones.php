<?php

use yii\db\Migration;

/**
 * Class m180530_082229_fix_customer_phones
 */
class m180530_082229_fix_customer_phones extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $query = \core\models\HistoryEntity::find()
            ->where(['>', 'created_time', '2018-05-27 00:00:00'])
            ->andWhere(['table_name' => '{{%customers}}']);

        $fixedCount = 0;
        foreach ($query->each() as $history){
            $log = $history->log;
            if(isset($log['new']['phone'])){
                if (strpos($log['new']['phone'], '* **') !== false) {
                    $customer = \core\models\customer\Customer::findOne($history->row_id);
                    $customer->phone = $log['old']['phone'] ?? '';
                    $customer->save();
                    echo "CHANGED " . $log['new']['phone']. " TO " . $customer->phone . "\n";
                    $fixedCount++;
                }
            }
        }

        echo "\nFIXED COUNT: {$fixedCount}\n";
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        return true;
    }
}
