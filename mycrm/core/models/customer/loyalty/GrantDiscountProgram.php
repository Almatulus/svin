<?php

namespace core\models\customer\loyalty;

use core\models\customer\CompanyCustomer;
use core\models\customer\CustomerLoyalty;

class GrantDiscountProgram implements LoyaltyProgramInterface
{
    /**
     * @param CustomerLoyalty $loyalty
     * @param CompanyCustomer $customer
     */
    public function process(CustomerLoyalty $loyalty, CompanyCustomer $customer)
    {
        if ($customer->discount < $loyalty->discount) {
            $customer->discount = $loyalty->discount;
            $customer->discount_granted_by = CompanyCustomer::GRANTED_BY_LOYALTY;
        }
    }
}