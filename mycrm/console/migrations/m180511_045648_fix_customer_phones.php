<?php

use core\models\customer\Customer;
use core\models\customer\CustomerHistory;
use yii\db\Migration;

/**
 * Class m180511_045648_fix_customer_phones
 */
class m180511_045648_fix_customer_phones extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        /* @var CustomerHistory[] $models */
        $models = CustomerHistory::find()
            ->andWhere([
                'AND',
                ['>=', 'created_time', '2018-05-10 11:00:00'],
                ['event' => 'afterUpdate']
            ])
            ->orderBy('created_time')
            ->all();

        foreach ($models as $model) {
            if (!isset($model->log['new']['phone'])) {
                continue;
            }

            if ($model->log['new']['phone'] == $model->log['old']['phone']) {
                continue;
            }

            if (empty($model->log['old']['phone'])) {
                continue;
            }

            if (!empty($model->log['new']['phone'])) {
                continue;
            }

            echo 'ID: ' . $model->row_id . "\n";
            echo 'Old: ' . $model->log['old']['phone'] . "\n";
            echo 'New: ' . $model->log['new']['phone'] . "\n";
            echo $model->created_time . "\n\n";

            $customer = Customer::findOne($model->row_id);
            $customer->phone = $model->log['old']['phone'];
            $customer->save();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {

    }
}
