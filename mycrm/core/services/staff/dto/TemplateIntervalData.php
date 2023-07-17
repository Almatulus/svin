<?php

namespace core\services\staff\dto;

class TemplateIntervalData
{
    public $day;
    public $start;
    public $end;
    public $break_start;
    public $break_end;

    public function __construct(
        int $day,
        string $start,
        string $end,
        string $break_start = null,
        string $break_end = null
    ) {
        $this->day = $day;
        $this->start = $start;
        $this->end = $end;
        $this->break_start = $break_start;
        $this->break_end = $break_end;
    }
}