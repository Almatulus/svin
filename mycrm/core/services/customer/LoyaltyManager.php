<?php

namespace core\services\customer;

use core\repositories\customer\CompanyCustomerRepository;
use core\repositories\customer\CustomerLoyaltyRepository;

/**
 * Class LoyaltyService
 * @package services\customer
 */
class LoyaltyManager
{
    /**
     * @var CompanyCustomerRepository
     * @var CustomerLoyaltyRepository
     */
    private $companyCustomerRepository;
    private $loyaltyRepository;

    /**
     * LoyaltyManager constructor.
     * @param CompanyCustomerRepository $companyCustomerRepository
     * @param CustomerLoyaltyRepository $loyaltyRepository
     */
    public function __construct(
        CompanyCustomerRepository $companyCustomerRepository,
        CustomerLoyaltyRepository $loyaltyRepository
    ) {
        $this->companyCustomerRepository = $companyCustomerRepository;
        $this->loyaltyRepository = $loyaltyRepository;
    }

    /**
     * @param int $customer_id
     */
    public function reward(int $customer_id)
    {
        $companyCustomer = $this->companyCustomerRepository->find($customer_id);

        $loyalties = $this->loyaltyRepository->findByCompany($companyCustomer->company_id);

        foreach ($loyalties as $loyalty) {
            $loyalty->process($companyCustomer);
        }

        $companyCustomer->save(false);
    }
}
