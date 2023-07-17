<?php

use core\models\customer\CompanyCustomer;
use core\helpers\order\OrderConstants;
use core\models\order\Order;
use yii\db\Migration;
use yii\helpers\ArrayHelper;

class m170923_112714_delete_redundant_balance extends Migration
{
    public function safeUp()
    {
        $companies = [103, 96];

        $company_customers
            = CompanyCustomer::find()
                             ->where(['company_id' => $companies])
                             ->all();

        Order::updateAll(['payment_difference' => 0], [
            'status'              => OrderConstants::STATUS_FINISHED,
            'company_customer_id' =>
                ArrayHelper::getColumn($company_customers, 'id')
        ]);

        CompanyCustomer::updateAll(
            ['balance' => 0],
            ['company_id' => $companies]
        );
    }

    public function safeDown()
    {
    }
}
