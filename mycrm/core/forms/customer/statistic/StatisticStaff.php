<?php

namespace core\forms\customer\statistic;

use core\forms\customer\StatisticStaffForm;
use core\helpers\order\OrderConstants;
use core\models\finance\CompanyCostItem;
use core\models\order\Order;
use core\models\order\OrderProduct;
use core\models\order\OrderService;
use core\models\order\query\OrderQuery;
use core\models\Staff;
use DateTime;

/**
 * @TODO Refactor. Move to ../forms folder
 * Class StatisticStaff
 * @package core\forms\statistic
 */
class StatisticStaff extends Staff
{
    public $ordered_time;
    public $revenue;

    /**
     * @var StatisticStaffForm
     */
    private $_formModel;

    /**
     * @param $model
     */
    public function setFormModel($model)
    {
        $this->_formModel = $model;
    }

    /**
     * @return int|string
     */
    public function getCanceledOrdersCount()
    {
        return $this->getOrdersQuery([OrderConstants::STATUS_DISABLED, OrderConstants::STATUS_CANCELED])->count();
    }

    /**
     * @return int|string
     */
    public function getOrdersCount()
    {
        return $this->getOrdersQuery(OrderConstants::STATUS_FINISHED)->count();
    }

    /**
     * @param $status
     * @return OrderQuery
     */
    private function getOrdersQuery($status)
    {
        $query = $this->getOrders()
            ->andWhere(":start_date <= datetime AND datetime < :finish_date",
                [
                    ":start_date"  => (new DateTime($this->_formModel->from))->format("Y-m-d"),
                    ":finish_date" => (new DateTime($this->_formModel->to))->modify("+1 day")->format("Y-m-d"),
                ])
            ->filterByStatus($status);

        if ($this->_formModel->service_category_id || $this->_formModel->service_id || $this->_formModel->service_categories) {
            $query->joinWith('orderServices.divisionService.categories', false)
                ->andFilterWhere(['crm_service_categories.id' => $this->_formModel->service_category_id])
                ->andFilterWhere(['crm_service_categories.id' => $this->_formModel->service_categories])
                ->andFilterWhere(['crm_order_services.division_service_id' => $this->_formModel->service_id]);
        }

        return $query;
    }

    /**
     * @return array
     */
    public function fields()
    {
        return [
            'id',
            'name',
            'surname',
            'revenue'       => function (self $model) {
                return $model->revenue ?: 0;
            },
            'position'      => 'companyPosition', // TODO возможно сериализация используется в api/v1
            'positions'     => 'companyPositions', // TODO Update in APIary
            'ordersCount',
            'canceledOrdersCount',
            'firstComeOrdersCount' => function(self $model) {
                return $model->getFirstComeOrdersCount();
            },
            'secondComeOrdersCount' => function(self $model) {
                return $model->getSecondComeOrdersCount();
            },
            'productsCount' => function (self $model) {
                return $model->getProductsCount();
            },
            'servicesCount' => function (self $model) {
                return $model->getServicesCount();
            },
            'workedHours'   => function () {
                return number_format(($this->getOrderedTime() / 60), 2, '.', ' ');
            },
            'revenueShare'  => function () {
                return number_format(($this->getRevenueShare()), 1);
            }
        ];
    }

    /**
     * @return int
     */
    public function getOrderedTime()
    {
        if (!$this->ordered_time) {
            $this->ordered_time = $this->_formModel->getOrderedTime($this->id);
        }
        return $this->ordered_time;
    }

    /**
     * @return float $averageTime, average order time in minutes
     */
    public function getAverageOrderedTime()
    {
        $services_count = $this->getServicesCount();
        return $services_count > 0 ? $this->getOrderedTime() / $services_count : 0;
    }

    /**
     * @return float|int
     */
    public function getRevenueShare()
    {
        return $this->_formModel->getTotalRevenue()
            ? $this->revenue / $this->_formModel->getTotalRevenue() * 100
            : 0;
    }

    public function getProductsCount()
    {
        $until = empty($this->_formModel->to) ? null : date('Y-m-d', strtotime('+1 day', strtotime($this->_formModel->to)));
        $query = OrderProduct::find()
            ->joinWith('order')
            ->andWhere([
                '{{%order_service_products}}.deleted_time' => null,
                '{{%orders}}.status' => OrderConstants::STATUS_FINISHED,
                '{{%orders}}.staff_id' => $this->id
            ])
            ->andFilterWhere(['>=', 'datetime', $this->_formModel->from])
            ->andFilterWhere(['<=', 'datetime', $until]);

        if ($this->_formModel->product_category_id || $this->_formModel->product_id || $this->_formModel->product_categories) {
            $query->joinWith('product', false);
            $query->andFilterWhere(['{{%warehouse_product}}.category_id' => $this->_formModel->product_category_id]);
            $query->andFilterWhere(['{{%warehouse_product}}.category_id' => $this->_formModel->product_categories]);
            $query->andFilterWhere(['{{%warehouse_product}}.id' => $this->_formModel->product_id]);
        }

        return intval($query->sum('{{%order_service_products}}.quantity'));
    }

    public function getServicesCount()
    {
        $until = empty($this->_formModel->to) ? null : date('Y-m-d', strtotime('+1 day', strtotime($this->_formModel->to)));
        $query = OrderService::find()
            ->joinWith('order')
            ->andWhere([
                '{{%order_services}}.deleted_time' => null,
                '{{%orders}}.status' => OrderConstants::STATUS_FINISHED,
                '{{%orders}}.staff_id' => $this->id
            ])
            ->andFilterWhere(['>=', 'datetime', $this->_formModel->from])
            ->andFilterWhere(['<=', 'datetime', $until]);

        if ($this->_formModel->service_category_id || $this->_formModel->service_id || $this->_formModel->service_categories) {
            $query->joinWith('divisionService.categories', false)
                ->andFilterWhere(['crm_service_categories.id' => $this->_formModel->service_category_id])
                ->andFilterWhere(['crm_service_categories.id' => $this->_formModel->service_categories])
                ->andFilterWhere(['{{%order_services}}.division_service_id' => $this->_formModel->service_id]);
        }

        return array_reduce($query->all(), function($counter, OrderService $model) {
            return $counter + $model->quantity;
        }, 0);
    }

    public function getTotalPaid()
    {
        $income = CompanyCostItem::find()
            ->innerJoinWith(['companyCashflows.order'])
            ->andWhere(['{{%company_cashflows}}.staff_id' => $this->id])
            ->orderPayment()
            ->income()
            ->andFilterWhere([
                '>=',
                '{{%company_cashflows}}.date',
                $this->_formModel->from
            ])
            ->andFilterWhere([
                '<',
                '{{%company_cashflows}}.date',
                $this->_formModel->to ?
                    ($this->_formModel->to . " 24:00:00") : null
            ])->sum('{{%company_cashflows}}.value');

        $expense = CompanyCostItem::find()
            ->innerJoinWith(['companyCashflows.order'])
            ->andWhere(['{{%company_cashflows}}.staff_id' => $this->id])
            ->orderPayment()
            ->expense()
            ->andFilterWhere([
                '>=',
                '{{%company_cashflows}}.date',
                $this->_formModel->from
            ])
            ->andFilterWhere([
                '<',
                '{{%company_cashflows}}.date',
                $this->_formModel->to ?
                    ($this->_formModel->to . " 24:00:00") : null
            ])->sum('{{%company_cashflows}}.value');

        return $income - $expense;
    }

    public function getFirstComeOrdersCount()
    {
        $query = $this->getOrdersQuery(OrderConstants::STATUS_FINISHED)
            ->joinWith('orderServices.divisionService', false)
            ->andWhere([
                '{{%order_services}}.deleted_time' => null,
                '{{%division_services}}.is_trial' => true
            ])
            ->groupBy('{{%orders}}.id');

        return $query->count();
    }

    public function getSecondComeOrdersCount()
    {
        $subQuery = $this->getOrdersQuery(OrderConstants::STATUS_FINISHED)
            ->select(['{{%orders}}.company_customer_id as company_customer_id'])
            ->joinWith('orderServices.divisionService', false)
            ->andWhere([
                '{{%order_services}}.deleted_time' => null,
                '{{%division_services}}.is_trial' => true
            ])
            ->groupBy('{{%orders}}.id');


        $query = $this->getOrdersQuery([OrderConstants::STATUS_FINISHED, OrderConstants::STATUS_ENABLED])
            ->select('DISTINCT {{%orders}}.company_customer_id')
            ->innerJoin(['f' => $subQuery], 'f.company_customer_id = {{%orders}}.company_customer_id')
            ->joinWith('orderServices.divisionService', false)
            ->andWhere([
                '{{%order_services}}.deleted_time' => null,
                '{{%division_services}}.is_trial' => false
            ])
            ->groupBy(['{{%orders}}.id']);

        return $query->count();
    }
}
