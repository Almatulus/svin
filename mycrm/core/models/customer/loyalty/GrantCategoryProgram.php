<?php

namespace core\models\customer\loyalty;

use core\models\customer\CompanyCustomer;
use core\models\customer\CustomerCategory;
use core\models\customer\CustomerLoyalty;

class GrantCategoryProgram implements LoyaltyProgramInterface
{
    /**
     * @param CustomerLoyalty $loyalty
     * @param CompanyCustomer $customer
     */
    public function process(CustomerLoyalty $loyalty, CompanyCustomer $customer)
    {
        if ($loyalty->category_id != null) {

            if ($customer->discount < $loyalty->category->discount) {
                $customer->discount = $loyalty->category->discount;
                $customer->discount_granted_by = CompanyCustomer::GRANTED_BY_CATEGORY;
            }

            if ($customer->cashback_percent < $loyalty->category->cashback_percent) {
                $customer->cashback_percent = $loyalty->category->cashback_percent;
            }

            $categories = $customer->categories;

            if (!$this->hasCategory($categories, $loyalty->category_id)) {
                $categories[] = $loyalty->category;
                $customer->categories = $categories;
            }
        }
    }

    /**
     * @param CustomerCategory[] $categories
     * @param int $category_id
     * @return bool
     */
    private function hasCategory($categories, int $category_id)
    {
        foreach ($categories as $category) {
            if ($category->id == $category_id) {
                return true;
            }
        }
        return false;
    }
}