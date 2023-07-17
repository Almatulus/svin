<?php

use core\models\finance\CompanyCash;
use core\models\order\Order;
use yii\db\Migration;

class m170506_103907_set_required_order_company_cash extends Migration
{
    public function safeUp()
    {
        /* @var Order[] $orders */
        $orders = Order::find()
            ->where(['company_cash_id' => null])
            ->joinWith(['staff.division.company'])
            ->orderBy('datetime')
            ->all();

        /* @var CompanyCash[] $companyCashes */
        $companyCashes = [];
        foreach ($orders as $order) {
            $company = $order->staff->division->company;
            if (!isset($companyCashs[$company->id])) {
                $companyCash = CompanyCash::find()
                    ->where(['company_id' => $company->id])
                    ->orderBy('id')
                    ->one();

                if ($companyCash == null) {
                    $companyCash = CompanyCash::add($company, Yii::t('app', 'Company Cash'), CompanyCash::TYPE_CASH_BOX, 0, null, false);
                    if (!$companyCash->insert(false)) {
                        throw new Exception('Company cash not found: ' . $company->name . "\n");
                    }
                }

                $companyCashes[$company->id] = $companyCash;
            }

            $order->company_cash_id = $companyCashes[$company->id]->id;
            $order->update(false);
            echo $order->datetime . "\n";
        }
        echo "Orders modified: " . count($orders) . "\n";

        $this->execute('ALTER TABLE {{%orders}} ALTER COLUMN company_cash_id SET NOT NULL');
    }

    public function safeDown()
    {
        $this->execute('ALTER TABLE {{%orders}} ALTER COLUMN company_cash_id DROP NOT NULL');
    }
}
