<?php

namespace core\services\dto;

class SalaryServiceData
{
    /** @var int */
    private $order_service_id;
    /** @var int */
    private $percent;
    /** @var int */
    private $sum;

    /**
     * SalaryServiceData constructor.
     * @param int $order_service_id
     * @param int $percent
     * @param int $sum
     */
    public function __construct(int $order_service_id, int $percent, int $sum)
    {
        $this->order_service_id = $order_service_id;
        $this->percent = $percent;
        $this->sum = $sum;
    }


    /**
     * @return int
     */
    public function getOrderServiceId(): int
    {
        return $this->order_service_id;
    }

    /**
     * @return int
     */
    public function getPercent(): int
    {
        return $this->percent;
    }

    /**
     * @return int
     */
    public function getSum(): int
    {
        return $this->sum;
    }
}