<?php

namespace core\repositories\order;

use core\helpers\order\OrderConstants;
use core\models\customer\CompanyCustomer;
use core\models\division\Division;
use core\models\order\Order;
use yii\db\ActiveQuery;

class OrderStatisticsRepository
{
    /**
     * @param $from
     * @param $to
     * @return ActiveQuery
     */
    private function getOrderQuery($from, $to)
    {
        $query = Order::find()
            ->joinWith(['division.company'])
            ->where(['>=', '{{%orders}}.created_time', $from])
            ->andWhere(['<=', '{{%orders}}.created_time', $to])
            ->andWhere(['NOT IN', '{{%divisions}}.company_id', OrderConstants::STATISTICS_EXCLUDED_COMPANIES]);

        return $query;
    }

    public function getActiveCompaniesCount($from, $to)
    {
       return $this->getOrderQuery($from, $to)
            ->select(['{{%divisions}}.company_id'])
            ->groupBy('{{%divisions}}.company_id')
            ->count();
    }

    public function getActiveCustomersCount($from, $to)
    {
        return $this->getOrderQuery($from, $to)
            ->select(['company_customer_id'])
            ->groupBy('company_customer_id')
            ->count();
    }

    public function getTotalOrdersCount($from, $to)
    {
        return $this->getOrderQuery($from, $to)
            ->count();
    }

    public function getActiveCompanyTotalCustomersCount($from, $to)
    {
        $orders = $this->getOrderQuery($from, $to)
            ->select('{{%orders}}.division_id')
            ->groupBy(['{{%orders}}.division_id']);

        $divisions = Division::find()->select('{{%divisions}}.company_id')
            ->where(['in', '{{%divisions}}.id', $orders])
            ->groupBy('{{%divisions}}.company_id');

        return CompanyCustomer::find()->where(['in', '{{%company_customers}}.company_id', $divisions])->count();
    }

    public function getTotalCustomersCount()
    {
        return CompanyCustomer::find()->andWhere([
            'NOT IN',
            'company_id',
            OrderConstants::STATISTICS_EXCLUDED_COMPANIES,
        ])->count();
    }

    public function getFinishedOrdersCount($from, $to)
    {
        return $this->getOrderQuery($from, $to)
            ->andWhere([
                'IN',
                '{{%orders}}.status',
                [
                    OrderConstants::STATUS_FINISHED,
                    OrderConstants::STATUS_ENABLED,
                ],
            ])
            ->count();
    }

    public function getTotalIncome($from, $to)
    {
        return $this->getOrderQuery($from, $to)
            ->andWhere([
                'IN',
                '{{%orders}}.status',
                [
                    OrderConstants::STATUS_FINISHED,
                    OrderConstants::STATUS_ENABLED,
                ],
            ])
            ->sum('price');
    }

    public function getOrdersArray($from, $to)
    {
        return $this->getOrderQuery($from, $to)
            ->asArray()
            ->all();
    }

    public function getActiveStuffsCount($from, $to)
    {
        return $this->getOrderQuery($from, $to)
            ->select(['staff_id'])
            ->groupBy('staff_id')
            ->count();
    }
}