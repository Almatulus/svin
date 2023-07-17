<?php

use core\models\customer\CompanyCustomer;
use core\helpers\order\OrderConstants;
use yii\db\Migration;

class m170823_203052_recalculate_debts extends Migration
{
    public function safeUp()
    {
        $this->update(CompanyCustomer::tableName(), ['balance' => 0]);
        $companyCustomers = CompanyCustomer::find()
            ->innerJoinWith('orders')
            ->andWhere(['!=', '{{%orders}}.payment_difference', 0])
            ->orderBy('company_id');

        foreach ($companyCustomers->each() as $model) {
            /* @var CompanyCustomer $model */
            $payment_difference = intval($model->getOrders()
                ->where(['status' => OrderConstants::STATUS_FINISHED])
                ->sum('payment_difference'));

            if ($model->balance !== $payment_difference) {
                echo $model->id;
                echo " ";
                echo $model->company->name;
                echo " ";
                echo $model->customer->getFullName();
                echo " ";
                echo $model->balance;
                echo " ";
                echo $payment_difference;
                echo "\n";
                $model->updateAttributes(['balance' => $payment_difference]);
            }
        }
    }

    public function safeDown()
    {
    }
}
