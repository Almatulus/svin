<?php

use core\models\customer\Customer;
use yii\db\Migration;

/**
 * Class m180515_105052_fix_customer_phones
 */
class m180515_105052_fix_customer_phones extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $logs = \core\models\HistoryEntity::find()
            ->where(['table_name' => '{{%customers}}'])
            ->andWhere(['event' => 'afterUpdate'])
            ->andWhere(['>=', 'created_time', '2018-05-09'])
            ->andWhere(['<=', 'created_time', '2018-05-11'])
            ->andWhere("json_extract_path_text(log, 'new') LIKE '%\"phone\":null%'")
            ->all();

        foreach ($logs as $log) {
            $model = Customer::findOne($log['row_id']);
            if (empty($model->phone)) {
                echo 'ID: ' . $log->row_id . "\n";
                echo 'Old: ' . $log->log['old']['phone'] . "\n";
                echo 'New: ' . $log->log['new']['phone'] . "\n";
                echo $log->created_time . "\n\n";

                $model->phone = $log->log['old']['phone'];
                if (!$model->save()) {
                    echo "Not saved!\n\n";
                };
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {

    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180515_105052_fix_customer_phones cannot be reverted.\n";

        return false;
    }
    */
}
