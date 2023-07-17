<?php

namespace core\forms\customer\statistic;

use core\models\division\DivisionService;
use core\models\division\query\DivisionServiceQuery;
use core\models\order\OrderService;
use core\models\order\query\OrderQuery;
use yii\base\Model;
use yii\db\ActiveQuery;
use yii\db\Expression;

/**
 * @TODO Refactor. Move to ../forms folder
 * Class StatisticService
 * @package core\forms\statistic
 *
 * @property integer $from
 * @property integer $to
 *
 * @property integer $orders_count
 * @property integer $services_count
 * @property integer $revenue
 * @property integer $average_cost
 */
class StatisticService extends DivisionService
{
    public $orders_count;
    public $services_count;
    public $revenue;
    public $totalRevenue;
    public $average_cost;
    public $revenueShare;

    public $from;
    public $to;
    public $category;
    public $division;
    public $division_service;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['from', 'to'], 'string'],
            [['division', 'category', 'division_service'], 'integer'],
        ];
    }

    /**
     *
     */
    public function init()
    {
        $this->to = date("Y-m-d");
        $this->from = date("Y-m-d", strtotime($this->to . "- 6 days"));
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
     * @return DivisionServiceQuery
     */
    public function getQuery()
    {
        $subQuery = $this->linkOrders(OrderService::find())
            ->select([
                "{{%order_services}}.division_service_id AS id",
                "COUNT({{%orders}}) AS orders_count",
                "SUM({{%order_services}}.quantity) AS services_count",
                "SUM({{%order_services}}.price*(100-{{%order_services}}.discount)/100) AS revenue",
            ])
            ->groupBy('{{%order_services}}.division_service_id');

        if ($this->category) {
            $subQuery->joinWith('divisionService.categories', false)
                ->andFilterWhere(['{{%service_categories}}.id' => $this->category]);
        }

        $divisions = $this->division ? [$this->division] : \Yii::$app->user->identity->getPermittedDivisions();
        $subQuery->andWhere(['{{%orders}}.division_id' => $divisions]);

        $subQuery->andFilterWhere(["{{%order_services}}.division_service_id" => $this->division_service]);

        return self::find()
            ->addSelect([
                '{{%division_services}}.id',
                'service_name',
                'orders_count',
                'services_count',
                'revenue',
                new Expression('revenue/orders_count AS average_cost'),
            ])
            ->innerJoin(['ds' => $subQuery], '{{%division_services}}.id = ds.id');
    }

    /**
     * @param ActiveQuery $query
     * @return ActiveQuery
     */
    public function linkOrders(ActiveQuery $query)
    {
        return $query->innerJoinWith([
            'order' => function (OrderQuery $orderQuery) {
                return $orderQuery->finished()
                    ->startFrom(new \DateTime($this->from), true)
                    ->to(new \DateTime($this->to));
            }
        ], false)->andWhere(['{{%order_services}}.deleted_time' => null]);
    }

    /**
     * @return string
     */
    public function formName()
    {
        return '';
    }

    public function fields()
    {
        return [
            'id',
            'name'        => 'service_name',
            'ordersCount' => 'orders_count',
            'servicesCount' => 'services_count',
            'revenue',
            'revenueShare' => function (self $model) {
                return number_format($model->revenueShare, 2);
            }
        ];
    }

    /**
     * @param StatisticService[] $models
     * @return array
     */
    public function getTop($models)
    {
        $mostPopular = null;
        $leastPopular = null;
        $maxRevenue = null;
        $totalRevenue = 0;

        if (sizeof($models) > 0) {
            $maxRevenue = $models[0];
            $leastPopular = $models[0];
            $mostPopular = $models[0];
        }

        foreach ($models as $model) {
            $totalRevenue += $model->revenue;

            if ($maxRevenue->revenue < $model->revenue) {
                $maxRevenue = $model;
            }

            if ($leastPopular->orders_count > $model->orders_count) {
                $leastPopular = $model;
            } else if ($leastPopular->orders_count == $model->orders_count) {
                if ($leastPopular->revenue > $model->revenue) {
                    $leastPopular = $model;
                }
            }

            if ($mostPopular->orders_count < $model->orders_count) {
                $mostPopular = $model;
            } else if ($mostPopular->orders_count == $model->orders_count) {
                if ($mostPopular->revenue < $model->revenue) {
                    $mostPopular = $model;
                }
            }
        }

        if (sizeof($models) > 0) {
            $maxRevenue->revenueShare = $totalRevenue == 0 ? 0 : $maxRevenue->revenue / $totalRevenue * 100;
            $mostPopular->revenueShare = $totalRevenue == 0 ? 0 : $mostPopular->revenue / $totalRevenue * 100;
            $leastPopular->revenueShare = $totalRevenue == 0 ? 0 : $leastPopular->revenue / $totalRevenue * 100;
        }

        return [
            'maxRevenue'   => $maxRevenue,
            'mostPopular'  => $mostPopular,
            'leastPopular' => $leastPopular,
        ];
    }
}