<?php

namespace core\services;

use core\helpers\DateHelper;
use core\repositories\customer\CustomerRequestRepository;
use core\repositories\order\OrderStatisticsRepository;
use core\services\dto\AdminStatisticsData;
use Yii;
use yii\helpers\ArrayHelper;

class AdminStatisticsService
{
    private $orders;
    private $smsRequests;

    public function __construct(
        OrderStatisticsRepository $orderStatisticsRepository,
        CustomerRequestRepository $customerRequestRepository
    )
    {
        $this->orders = $orderStatisticsRepository;
        $this->smsRequests = $customerRequestRepository;
    }

    private function getRangedOrdersCount($from, $to)
    {
        $data = [];
        $orders = $this->orders->getOrdersArray($from, $to);

        $orders = ArrayHelper::index($orders, null, function ($element) {
            return Yii::$app->formatter->asDate($element['created_time'], 'php:Y-m-d');
        });

        foreach ($this->getRange($from, $to) as $key => $date) {
            $count = isset($orders[$date]) ? count($orders[$date]) : 0;
            $data[] = $count;
        }

        return $data;
    }

    private function getRange($from, $to)
    {
        return DateHelper::date_range($from, $to);
    }

    public function getMainStatisticsData($from, $to)
    {
        return new AdminStatisticsData(
            $this->orders->getActiveCompaniesCount($from, $to),
            $this->orders->getActiveCustomersCount($from, $to),
            $this->smsRequests->getSentCount($from, $to),
            $this->orders->getTotalOrdersCount($from, $to),
            $this->orders->getFinishedOrdersCount($from, $to),
            $this->getRangedOrdersCount($from, $to),
            $this->getRange($from, $to),
            $this->orders->getActiveStuffsCount($from, $to),
            $this->orders->getTotalCustomersCount(),
            $this->orders->getTotalIncome($from, $to)
        );
    }
}
