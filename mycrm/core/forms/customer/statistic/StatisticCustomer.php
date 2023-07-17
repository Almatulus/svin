<?php

namespace core\forms\customer\statistic;

use core\models\customer\CompanyCustomer;
use core\models\customer\query\CompanyCustomerQuery;
use core\models\customer\query\CustomerCategoryQuery;
use core\models\order\Order;
use core\models\order\query\OrderQuery;
use yii\base\Model;

/**
 * @TODO Refactor. Move to ../forms folder
 * Class StatisticCustomer
 * @package core\forms\statistic
 *
 * @property integer $from
 * @property integer $to
 *
 * @property Order[] $filterOrders
 * @property integer $ordersCount
 * @property integer $totalRevenue
 * @property integer $averageRevenue
 * @property integer $lastOrderDay
 */
class StatisticCustomer extends CompanyCustomer
{
    public $average_revenue;
    public $revenue;
    public $orders_count;
    public $revenueShare;

    public $from;
    public $to;
    public $category;
    public $division;

    public function rules()
    {
        return [
            [['from', 'to'], 'string'],
            [['division', 'category'], 'integer'],
        ];
    }

    public function init()
    {
        $this->to = date("Y-m-d");
        $this->from = date("Y-m-d", strtotime($this->to . " -6 days"));
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * @return CompanyCustomerQuery
     */
    public function getQuery()
    {
        $subQuery = StatisticCustomer::find()
            ->company()
            ->select([
                "{{%company_customers}}.id as id",
                "COUNT({{%orders}}) AS orders_count",
                "SUM({{%orders}}.price) AS revenue"
            ])
            ->joinWith([
                'orders' => function (OrderQuery $query) {
                    $query->permitted()
                        ->finished()
                        ->startFrom(new \DateTime($this->from))
                        ->to(new \DateTime($this->to))
                        ->division($this->division);
                }
            ])->groupBy('{{%company_customers}}.id');

        if ($this->category) {
            $subQuery->joinWith([
                'categories' => function (CustomerCategoryQuery $query) {
                    return $query->andFilterWhere(['{{%company_customer_categories}}.id' => $this->category]);
                }
            ]);
        }

        return self::find()
            ->addSelect([
                '{{%company_customers}}.*',
                '(revenue/orders_count) as average_revenue',
                'revenue',
                'orders_count'
            ])
            ->innerJoin(['ds' => $subQuery], '{{%company_customers}}.id = ds.id')
            ->company();
    }

    /**
     * @return array
     */
    public function fields()
    {
        return [
            'id',
            'fullName'     => function () {
                return $this->customer->getFullName();
            },
            'phone'        => function () {
                return $this->customer->phone;
            },
            'averageCheck' => 'average_revenue',
            'ordersCount'  => 'orders_count',
            'revenue',
            'revenueShare'
        ];
    }

    /**
     * @param StatisticCustomer[] $models
     * @return array
     */
    public function getTop($models)
    {
        $maxVisits = null;
        $maxRevenue = null;
        $maxDebt = null;

        if (sizeof($models) > 0) {
            $maxVisits = $models[0];
            $maxRevenue = $models[0];
            $maxDebt = $models[0];
        }

        foreach ($models as $model) {
            if ($maxVisits->orders_count < $model->orders_count) {
                $maxVisits = $model;
            }

            if ($maxRevenue->revenue < $model->revenue) {
                $maxRevenue = $model;
            }

            if ($model->balance < 0 && $maxDebt->balance < $model->balance) {
                $maxDebt = $model;
            }
        }

        return [
            'maxVisits'  => $maxVisits,
            'maxRevenue' => $maxRevenue,
            'maxDebt'    => ($maxDebt && $maxDebt->balance < 0) ? $maxDebt : null,
        ];
    }
}