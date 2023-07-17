<?php

namespace services;

use core\models\order\Order;
use core\services\AdminStatisticsService;
use core\services\dto\AdminStatisticsData;

class AdminStatisticsServiceTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    /** @var AdminStatisticsService */
    protected $service;

    public function testGetMainStatisticsData()
    {
        $to = new \DateTimeImmutable();
        $from = $to->modify("-2 weeks");

        $uniqueOrdersCount = 10;
        $orders = $this->tester->getFactory()->seed($uniqueOrdersCount, Order::class, [
            'created_time' => $to->modify("-1 week")->format("Y-m-d H:i:s")
        ]);

        $finishedOrdersCount = sizeof(array_filter($orders, function (Order $order) {
            return $order->isFinished();
        }));

        $totalIncome = array_reduce(array_filter($orders, function (Order $order) {
            return $order->isFinished() || $order->isEnabled();
        }), function (int $total, Order $order) {
            return $total + $order->price;
        }, 0);

        $adminStatisticsData = $this->service->getMainStatisticsData($from->format("Y-m-d"), $to->format("Y-m-d"));

        verify($adminStatisticsData)->isInstanceOf(AdminStatisticsData::class);
        verify($adminStatisticsData->activeStuffsCount)->equals($uniqueOrdersCount);
        verify($adminStatisticsData->activeCustomersCount)->equals($uniqueOrdersCount);
        verify($adminStatisticsData->activeCompaniesCount)->equals($uniqueOrdersCount);
        verify($adminStatisticsData->totalOrdersCount)->equals($uniqueOrdersCount);
        verify($adminStatisticsData->finishedOrdersCount)->equals($finishedOrdersCount);
        verify($adminStatisticsData->income)->equals($totalIncome);
    }

    protected function _before()
    {
        $this->service = \Yii::createObject(AdminStatisticsService::class);
    }

    // tests

    protected function _after()
    {
    }
}