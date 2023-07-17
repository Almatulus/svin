<?php

use yii\db\Migration;

class m170514_213256_uq_order_payments extends Migration
{
    public function safeUp()
    {
        $sql = <<<SQL
            SELECT order_id, payment_id, count(*) as count 
            FROM {{%order_payments}} 
            GROUP BY order_id, payment_id 
            HAVING count(*) > 1
SQL;
        $orderPayments = Yii::$app->db->createCommand($sql)->queryAll();

        foreach ($orderPayments as $orderPayment) {
            echo $orderPayment['order_id'] . " " . $orderPayment['payment_id'] . " " . $orderPayment['count'] . "\n";
            $sql = <<<SQL
                SELECT * 
                FROM {{%order_payments}} 
                WHERE 
                order_id = :order_id AND  
                payment_id = :payment_id
                ORDER BY id DESC
SQL;
            $orderItems = Yii::$app->db->createCommand($sql, [
                ':order_id' => $orderPayment['order_id'],
                ':payment_id' => $orderPayment['payment_id']
            ])->queryAll();

            $i = 0;
            foreach ($orderItems as $orderItem) {
                if ($i++ == 0) {
                    continue;
                }
                $this->delete('{{%order_payments}}', 'id = :id', [':id' => $orderItem['id']]);
            }
        }
        $this->createIndex('uq_order_payments_order_payment', '{{%order_payments}}', ['payment_id', 'order_id'], true);

        $this->execute('UPDATE {{%order_service_products}} SET selling_price = abs(selling_price)');
    }

    public function safeDown()
    {
        $this->dropIndex('uq_order_payments_order_payment', '{{%order_payments}}');
    }
}
