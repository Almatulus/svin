<?php

namespace core\services\order\dto;

use DateTime;

/**
 * @property DateTime $datetime
 * @property integer  $staff_id
 * @property integer  $division_id
 * @property integer  $notify_hours_before
 * @property integer  $created_user_id
 * @property integer  $company_id
 * @property string   $note
 * @property string   $color
 * @property integer  $company_cash_id
 * @property integer  $insurance_company_id
 * @property integer  $referrer_id
 */
class OrderData
{
    public $staff_id;
    public $note;
    public $notify_hours_before;
    public $created_user_id;
    public $company_id;
    public $color;
    public $company_cash_id;
    public $insurance_company_id;
    public $referrer_id;
    public $division_id;
    public $datetime;

    public function __construct(
        DateTime $datetime,
        $division_id,
        $staff_id,
        $note,
        $notify_hours_before,
        $color,
        $company_cash_id,
        $created_user_id,
        $company_id,
        $insurance_company_id,
        $referrer_id
    ) {
        $this->division_id         = $division_id;
        $this->datetime            = $datetime;
        $this->staff_id            = $staff_id;
        $this->note                = $note;
        $this->notify_hours_before = $notify_hours_before;
        $this->created_user_id     = $created_user_id;
        $this->company_id          = $company_id;
        $this->color               = $color;
        $this->company_cash_id     = $company_cash_id;
        $this->insurance_company_id        = $insurance_company_id;
        $this->referrer_id         = $referrer_id;
    }

    /**
     * @param string $format
     *
     * @return string
     */
    public function getDatetime($format = 'Y-m-d H:i:s')
    {
        return $this->datetime->format($format);
    }
}