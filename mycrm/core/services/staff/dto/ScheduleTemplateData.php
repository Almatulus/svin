<?php

namespace core\services\staff\dto;

class ScheduleTemplateData
{
    /** @var int */
    public $staff_id;
    /** @var int */
    public $division_id;
    /** @var int */
    public $interval_type;
    /** @var int */
    public $type;

    /**
     * ScheduleTemplateData constructor.
     * @param $staff_id
     * @param $division_id
     * @param $interval_type
     * @param $type
     */
    public function __construct(int $staff_id, int $division_id, int $interval_type, int $type)
    {
        $this->staff_id = $staff_id;
        $this->division_id = $division_id;
        $this->interval_type = $interval_type;
        $this->type = $type;
    }

}