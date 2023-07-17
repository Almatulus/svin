<?php

use core\models\order\Order;
use yii\db\Migration;

/**
 * Class m180326_085605_normalize_order_insurances
 */
class m180326_085605_fix_order_insurances extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->update('{{%payments}}', ['type' => \core\helpers\company\PaymentHelper::INSURANCE], [
            'name' => 'insurance'
        ]);

        $orders = Order::find()->innerJoinWith('orderPayments.payment')
            ->andWhere(['{{%payments}}.name' => 'insurance'])
            ->andWhere(['>', '{{%order_payments}}.amount', 0])
            ->orderBy('datetime ASC');

        foreach ($orders->each(100) as $order) {
            /** @var Order $order */
            if (!$order->insurance_company_id && $order->companyCustomer->insurance_company_id) {
                echo "order = {$order->id} | company_customer = {$order->companyCustomer->id} | " .
                    "insurance_company = {$order->companyCustomer->insurance_company_id} from customer" . PHP_EOL;

                $order->insurance_company_id = $order->companyCustomer->insurance_company_id;
                $order->save(false);
            }

            if (!$order->companyCustomer->insurance_company_id && $order->insurance_company_id) {
                echo "order = {$order->id} | company_customer = {$order->companyCustomer->id} | " .
                    "insurance_company = {$order->insurance_company_id} from order" . PHP_EOL;
                $order->companyCustomer->insurance_company_id = $order->insurance_company_id;
                $order->companyCustomer->save(false);
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->update('{{%payments}}', ['type' => null], [
            'type' => \core\helpers\company\PaymentHelper::INSURANCE
        ]);
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180326_085605_normalize_order_insurances cannot be reverted.\n";

        return false;
    }
    */
}
