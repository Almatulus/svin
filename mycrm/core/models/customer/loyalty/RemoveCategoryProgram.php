<?php

namespace core\models\customer\loyalty;

use core\models\customer\CompanyCustomer;
use core\models\customer\CustomerCategory;
use core\models\customer\CustomerLoyalty;

class RemoveCategoryProgram implements LoyaltyProgramInterface
{
    /**
     * @param CustomerLoyalty $loyalty
     * @param CompanyCustomer $customer
     */
    public function process(CustomerLoyalty $loyalty, CompanyCustomer $customer)
    {
        $category_id = $loyalty->category_id;
        if ($category_id !== null) {

            $customer->categories = array_filter($customer->categories,
                function (CustomerCategory $category) use ($category_id) {
                    return $category->id !== $category_id;
                });
        }
    }
}