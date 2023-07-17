<?php

namespace core\services\dto;

class CustomerInsuranceData
{
    public $insurance_company_id;
    public $insurance_policy_number;
    public $insurer;
    private $insurance_expire_date;

    /**
     * CustomerInsuranceData constructor.
     * @param $insurance_company_id
     * @param $insurance_expire_date
     * @param $insurance_policy_number
     * @param $insurer
     */
    public function __construct(
        int $insurance_company_id = null,
        \DateTime $insurance_expire_date = null,
        string $insurance_policy_number = null,
        string $insurer = null
    ) {
        $this->insurance_company_id = $insurance_company_id;
        $this->insurance_expire_date = $insurance_expire_date;
        $this->insurance_policy_number = $insurance_policy_number;
        $this->insurer = $insurer;
    }

    /**
     * @return \DateTime|string
     */
    public function getInsuranceExpireDate()
    {
        if ($this->insurance_expire_date) {
            return $this->insurance_expire_date->format("Y-m-d");
        }
        return $this->insurance_expire_date;
    }
}