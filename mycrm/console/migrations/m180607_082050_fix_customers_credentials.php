<?php

use core\models\customer\Customer;
use core\models\customer\CustomerHistory;
use yii\db\Migration;

/**
 * Class m180607_082050_fix_customers_credentials
 */
class m180607_082050_fix_customers_credentials extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $histories = CustomerHistory::find()
        ->andWhere([
            'class' => 'core\models\customer\Customer',
            'event' => 'afterUpdate'
        ])
            ->andWhere([
                '>',
                'created_time',
                '2018-06-01 00:00:00'
            ])
        ->all();

        foreach ($histories as $history) {
            $old = $history->log['old'];
            $new = $history->log['new'];

            if (isset($old['lastname']) && isset($new['lastname'])) {

                $has_changed = empty($new['lastname']) || strpos(strtolower($old['lastname']), strval(strtolower($new['lastname']))) !== false;

                if ($has_changed) {
                    echo $history->row_id . " => " . $old['lastname'] . " === " . $new['lastname'] . " lastname \n";
                    $this->update(Customer::tableName(), [
                        'lastname' => $old['lastname']
                    ], [
                        'id' => $history->row_id
                    ]);
                }
            }

            if (isset($old['name']) && isset($new['name'])) {

                $has_changed = empty($new['name']) || strpos(strtolower($old['name']), strval(strtolower($new['name']))) !== false;

                if ($has_changed) {
                    echo $history->row_id . " => " . $old['name'] . " === " . $new['name'] . " name \n";
                    $this->update(Customer::tableName(), [
                        'name' => $old['name']
                    ], [
                        'id' => $history->row_id
                    ]);
                }
            }

            if (isset($old['patronymic']) && isset($new['patronymic'])) {

                $has_changed = empty($new['patronymic']) || strpos(strtolower($old['patronymic']), strval(strtolower($new['patronymic']))) !== false;

                if ($has_changed) {
                    echo $history->row_id . " => " . $old['patronymic'] . " === " . $new['patronymic'] . " patronymic \n";
                    $this->update(Customer::tableName(), [
                        'patronymic' => $old['patronymic']
                    ], [
                        'id' => $history->row_id
                    ]);
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {

    }
}
