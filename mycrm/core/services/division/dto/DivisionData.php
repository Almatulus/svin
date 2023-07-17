<?php

namespace core\services\division\dto;

/**
 * @property string $address
 * @property int    $category_id
 * @property int    $company_id
 * @property int    $city_id
 * @property string $description
 * @property float  $latitude
 * @property float  $longitude
 * @property string $name
 * @property int    $status
 * @property string $url
 * @property string $working_finish
 * @property string $working_start
 * @property int    $logo_id
 * @property int    $default_notification_time
 */
class DivisionData
{
    public $address;
    public $category_id;
    public $company_id;
    public $city_id;
    public $description;
    public $latitude;
    public $longitude;
    public $name;
    public $status;
    public $url;
    public $working_finish;
    public $working_start;
    public $logo_id;
    public $default_notification_time;

    /**
     * DivisionData constructor.
     *
     * @param string $address
     * @param int    $category_id
     * @param int    $company_id
     * @param int    $city_id
     * @param string $description
     * @param float  $latitude
     * @param float  $longitude
     * @param string $name
     * @param int    $status
     * @param string $url
     * @param string $working_finish
     * @param string $working_start
     * @param int    $logo_id
     * @param int    $default_notification_time
     */
    public function __construct(
        string $address,
        int $category_id,
        int $company_id,
        int $city_id,
        string $description,
        float $latitude,
        float $longitude,
        string $name,
        int $status,
        string $url,
        string $working_finish,
        string $working_start,
        $default_notification_time,
        $logo_id
    ) {
        $this->address                   = $address;
        $this->category_id               = $category_id;
        $this->company_id                = $company_id;
        $this->city_id                   = $city_id;
        $this->description               = $description;
        $this->latitude                  = $latitude;
        $this->longitude                 = $longitude;
        $this->name                      = $name;
        $this->status                    = $status;
        $this->url                       = $url;
        $this->working_finish            = $working_finish;
        $this->working_start             = $working_start;
        $this->logo_id                   = $logo_id;
        $this->default_notification_time = $default_notification_time;
    }
}
