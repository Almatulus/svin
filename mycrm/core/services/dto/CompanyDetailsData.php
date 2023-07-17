<?php

namespace core\services\dto;

/**
 * @property string  $address
 * @property string  $bank
 * @property string  $bik
 * @property string  $bin
 * @property string  $iik
 * @property string  $license_issued
 * @property string  $license_number
 * @property string  $name
 * @property string  $phone
 * @property string  $widget_prefix
 * @property string  $online_start
 * @property string  $online_finish
 * @property integer $logo_id
 */

class CompanyDetailsData
{
    public $address;
    public $bank;
    public $bik;
    public $bin;
    public $iik;
    public $license_issued;
    public $license_number;
    public $name;
    public $phone;
    public $widget_prefix;
    public $online_start;
    public $online_finish;
    public $logo_id;

    public function __construct(
        $address,
        $bank,
        $bik,
        $bin,
        $iik,
        $license_issued,
        $license_number,
        $name,
        $phone,
        $widget_prefix = null,
        $online_start = null,
        $online_finish = null,
        $logo_id = null
    ) {
        $this->address        = $address;
        $this->bank           = $bank;
        $this->bik            = $bik;
        $this->bin            = $bin;
        $this->iik            = $iik;
        $this->license_issued = $license_issued;
        $this->license_number = $license_number;
        $this->name           = $name;
        $this->phone          = $phone;
        $this->widget_prefix  = $widget_prefix;
        $this->online_start   = $online_start;
        $this->online_finish  = $online_finish;
        $this->logo_id        = $logo_id;
    }
}