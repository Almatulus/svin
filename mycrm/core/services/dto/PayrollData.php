<?php

namespace core\services\dto;

class PayrollData
{
    public $company_id;
    public $is_count_discount;
    public $name;
    public $salary;
    public $salary_mode;
    public $service_mode;
    public $service_value;

    /**
     * PayrollData constructor.
     * @param $company_id
     * @param $is_count_discount
     * @param $name
     * @param $salary
     * @param $salary_mode
     * @param $service_mode
     * @param $service_value
     */
    public function __construct($company_id, $is_count_discount, $name,
                                $salary, $salary_mode, $service_mode, $service_value)
    {
        $this->company_id = $company_id;
        $this->is_count_discount = $is_count_discount;
        $this->name = $name;
        $this->salary = $salary;
        $this->salary_mode = $salary_mode;
        $this->service_mode = $service_mode;
        $this->service_value = $service_value;
    }
}