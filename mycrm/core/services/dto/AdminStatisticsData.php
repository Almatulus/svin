<?php

namespace core\services\dto;

/**
 * Class AdminStatisticsData
 *
 * @package core\services\dto
 */

class AdminStatisticsData
{
    public $activeCompaniesCount;
    public $activeCustomersCount;
    public $sentSmsCount;
    public $totalOrdersCount;
    public $finishedOrdersCount;
    public $rangedOrders;
    public $range;
    public $activeStuffsCount;
    public $totalCustomersCount;
    public $income;

    public function __construct(
        $activeCompaniesCount,
        $activeCustomersCount,
        $sentSmsCount,
        $totalOrdersCount,
        $finishedOrdersCount,
        $rangedOrders,
        $range,
        $activeStaffsCount,
        $totalCustomersCount,
        $income
    ) {
        $this->activeCompaniesCount = $activeCompaniesCount;
        $this->activeCustomersCount = $activeCustomersCount;
        $this->sentSmsCount         = $sentSmsCount;
        $this->totalOrdersCount     = $totalOrdersCount;
        $this->finishedOrdersCount  = $finishedOrdersCount;
        $this->rangedOrders         = $rangedOrders;
        $this->range                = $range;
        $this->activeStuffsCount    = $activeStaffsCount;
        $this->totalCustomersCount  = $totalCustomersCount;
        $this->income = $income;
    }
}
