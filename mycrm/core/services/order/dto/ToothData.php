<?php

namespace core\services\order\dto;

/**
 * @property integer $number;
 * @property integer $type;
 * @property integer $diagnosis_id;
 * @property integer $mobility;
 */
class ToothData
{
    public $number;
    public $type;
    public $diagnosis_id;
    public $mobility;

    public function __construct(int $number, int $type, int $diagnosis_id = null, int $mobility = null)
    {
        $this->number = $number;
        $this->type = $type;
        $this->diagnosis_id = $diagnosis_id;
        $this->mobility = $mobility;
    }
}