<?php

namespace core\forms\division;

use core\models\division\Division;

class DivisionUpdateForm extends DivisionCreateForm
{
    public $division;

    /**
     * DivisionUpdateForm constructor.
     * @param Division $division
     * @param array $config
     */
    public function __construct(Division $division, $config = [])
    {
        parent::__construct($config);

        $this->division = $division;

        $this->address = $division->address;
        $this->category_id = $division->category_id;
        $this->company_id = $division->company_id;
        $this->city_id = $division->city_id;
        $this->description = $division->description;
        $this->latitude = $division->latitude;
        $this->longitude = $division->longitude;
        $this->name = $division->name;
        $this->status = $division->status;
        $this->url = $division->url;
        $this->working_finish = $division->working_finish;
        $this->working_start = $division->working_start;
        $this->logo_id = $division->logo_id;
        $this->default_notification_time = $division->default_notification_time;

        $this->country_id = $division->city->country_id;
        $this->payments = $division->getDivisionPayments()->select('payment_id')->column();
        $phones = $division->getDivisionPhones()->select('value')->column();
        $this->phones = empty($phones) ? [""] : $phones;

        $this->notification_time_before_lunch = $division->settings->notification_time_before_lunch ?? null;
        $this->notification_time_after_lunch = $division->settings->notification_time_after_lunch ?? null;
    }

    // override
    public function init()
    {

    }

}