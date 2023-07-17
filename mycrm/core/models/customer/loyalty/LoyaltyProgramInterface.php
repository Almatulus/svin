<?php

namespace core\models\customer\loyalty;

use core\models\customer\CompanyCustomer;
use core\models\customer\CustomerLoyalty;

interface LoyaltyProgramInterface
{
    /**
     * @param CustomerLoyalty $loyalty
     * @param CompanyCustomer $customer
     */
    public function process(CustomerLoyalty $loyalty, CompanyCustomer $customer);
}