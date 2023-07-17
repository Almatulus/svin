<?php

use core\models\division\DivisionPayment;
use core\models\order\Order;
use core\models\order\OrderPayment;
use yii\db\Migration;

class m170823_123851_order_payment_difference extends Migration
{
    private $divisionPayments = [];

    public function safeUp()
    {
        $this->addColumn(
            '{{%orders}}',
            'payment_difference',
            $this->integer()->notNull()->defaultValue(0)
        );

        $query = Order::find()->orderBy('datetime');

        echo "Orders:\n";
        foreach ($query->each() as $model) {
            /* @var Order $model */
            echo "{$model->id} <=> {$model->datetime} => |{$model->payment_difference}| ";

            $order_payment = 0;
            $division_id = $model->staff->division_id;
            $divisionPayments = $this->getDivisionPayments($division_id);
            if (count($divisionPayments) > 0) {
                $order_payment = array_reduce(
                    $model->orderPayments,
                    function ($sum, OrderPayment $model) {
                        return $sum + $model->amount;
                    },
                    0
                );
            }

            $model->updateAttributes(['payment_difference' => $order_payment - $model->price]);
            echo "|{$order_payment}|\n";
        }
    }

    public function safeDown()
    {
        $this->dropColumn(
            '{{%orders}}',
            'payment_difference'
        );
    }

    private function getDivisionPayments($division_id)
    {
        if (!isset($this->divisionPayments[$division_id])) {
            $this->divisionPayments[$division_id] = DivisionPayment::find()
                ->orderBy('payment_id')
                ->where(['division_id' => $division_id])
                ->all();
        }

        return $this->divisionPayments[$division_id];
    }
}
