<?php

namespace core\services\order;

use core\models\customer\CompanyCustomer;
use core\models\customer\Customer;
use core\models\customer\CustomerCategory;
use core\models\order\{
    OrderPayment, OrderProduct, OrderService
};
use core\repositories\CompanyCashflowRepository;
use core\repositories\CompanyCashRepository;
use core\repositories\customer\CompanyCustomerCategoryRepository;
use core\repositories\customer\CompanyCustomerRepository;
use core\repositories\CustomerRepository;
use core\repositories\division\DivisionPaymentRepository;
use core\repositories\division\DivisionRepository;
use core\repositories\DivisionServiceRepository;
use core\repositories\exceptions\NotFoundException;
use core\repositories\order\{
    OrderPaymentRepository, OrderProductRepository, OrderRepository, OrderServiceRepository
};
use core\repositories\PaymentRepository;
use core\repositories\StaffRepository;
use core\repositories\warehouse\ProductRepository;
use core\repositories\warehouse\UsageRepository;
use core\services\dto\CustomerData;
use core\services\TransactionManager;

abstract class OrderServiceAbstract
{
    protected $orderRepository;
    protected $companyCustomerRepository;
    protected $companyCustomerCategoryRepository;
    protected $customerRepository;
    protected $transactionManager;
    protected $orderServiceRepository;
    protected $orderPaymentRepository;
    protected $orderProductRepository;
    protected $productRepository;
    protected $divisionServiceRepository;
    protected $paymentRepository;
    protected $companyCashRepository;
    protected $companyCashflowRepository;
    protected $staffRepository;
    protected $usageRepository;
    protected $divisionPaymentRepository;
    protected $divisionRepository;

    public function __construct(
        OrderRepository $orderRepository,
        CompanyCustomerRepository $companyCustomerRepository,
        CompanyCustomerCategoryRepository $companyCustomerCategoryRepository,
        CustomerRepository $customerRepository,
        OrderProductRepository $orderProductRepository,
        OrderServiceRepository $orderServiceRepository,
        OrderPaymentRepository $orderPaymentRepository,
        DivisionServiceRepository $divisionServiceRepository,
        ProductRepository $productRepository,
        PaymentRepository $paymentRepository,
        StaffRepository $staffReviewRepository,
        CompanyCashRepository $companyCashRepository,
        CompanyCashflowRepository $companyCashflowRepository,
        UsageRepository $usageRepository,
        DivisionPaymentRepository $divisionPaymentRepository,
        DivisionRepository $divisionRepository,
        TransactionManager $transactionManager
    )
    {
        $this->companyCustomerRepository = $companyCustomerRepository;
        $this->companyCustomerCategoryRepository = $companyCustomerCategoryRepository;
        $this->orderRepository = $orderRepository;
        $this->customerRepository = $customerRepository;
        $this->orderPaymentRepository = $orderPaymentRepository;
        $this->orderProductRepository = $orderProductRepository;
        $this->orderServiceRepository = $orderServiceRepository;
        $this->divisionServiceRepository = $divisionServiceRepository;
        $this->transactionManager = $transactionManager;
        $this->paymentRepository = $paymentRepository;
        $this->productRepository = $productRepository;
        $this->companyCashRepository = $companyCashRepository;
        $this->companyCashflowRepository = $companyCashflowRepository;
        $this->staffRepository = $staffReviewRepository;
        $this->usageRepository = $usageRepository;
        $this->divisionPaymentRepository = $divisionPaymentRepository;
        $this->divisionRepository = $divisionRepository;
    }

    /**
     * Calculates duration by services
     * @param OrderService[] $orderServices
     * @return integer
     */
    final protected function getCalculatedDuration($orderServices)
    {
        return array_reduce($orderServices, function ($duration, OrderService $orderService) {
            return $duration + $orderService->duration;
        }, 0);
    }

    /**
     * Calculates duration by services
     * @param OrderService[] $orderServices
     * @param OrderProduct[] $orderProducts
     * @return int
     */
    final protected function getCalculatedPrice($orderServices, $orderProducts)
    {
        return array_reduce($orderServices, function ($servicesPrice, OrderService $orderService) {
            return $orderService->getFinalPrice() + $servicesPrice;
        }, array_reduce($orderProducts,
            function ($productsPrice, OrderProduct $orderProduct) {
                return $productsPrice + $orderProduct->getTotalSellingPrice();
        }, 0));
    }

    /**
     * Returns company customer by phone number
     *
     * @param CustomerData $customerData
     * @param integer      $company_id
     *
     * @return CompanyCustomer
     * @throws \Exception
     */
    final protected function getCompanyCustomer(CustomerData $customerData, $company_id)
    {
        self::guardCustomerData($customerData);

        try {
            if (empty($customerData->company_customer_id)) {
                $companyCustomer = null;
                if (!empty($customerData->phone) && !empty($customerData->name)) {
                    $companyCustomer = $this->companyCustomerRepository->findByPhoneAndName(
                        $customerData->phone,
                        $customerData->name,
                        $customerData->surname,
                        $company_id
                    );
                }

                if (!$companyCustomer) {
                    throw new NotFoundException('Empty company_customer_id');
                }
            } else {
                $companyCustomer = $this->companyCustomerRepository->find($customerData->company_customer_id);
            }

            $customer = $companyCustomer->customer;
            $customer->rename($customerData->name, $customerData->surname, $customerData->patronymic);
            $phone = empty($customerData->phone) ? $customer->phone : $customerData->phone;
            $customer->edit(
                $phone,
                $customer->email,
                $customerData->gender !== null ? $customerData->gender : $customer->gender,
                $customerData->birth_date !== null ? $customerData->birth_date : $customer->birth_date,
                $customer->iin,
                $customer->id_card_number
            );
        } catch (NotFoundException $e) {
            $customer = Customer::add(
                $customerData->phone,
                $customerData->name,
                $customerData->surname,
                $customerData->gender,
                $customerData->birth_date,
                null,
                null,
                null,
                $customerData->patronymic
            );

            $companyCustomer = CompanyCustomer::add(
                $customer,
                $company_id,
                0,
                true,
                false,
                null,
                null,
                null,
                null,
                0,
                null,
                null,
                null,
                null,
                $customerData->medical_record_id
            );
        }

        $companyCustomer->insurance_company_id = $customerData->insurance_company_id ?: $companyCustomer->insurance_company_id;
        if ($customerData->categories !== null) {
            $companyCustomer->categories = $this->getCategories($customerData->categories);
        }

        $this->transactionManager->execute(function () use ($companyCustomer, $customer) {
            $this->customerRepository->save($customer);
            $this->companyCustomerRepository->save($companyCustomer);
        });

        return $companyCustomer;
    }

    /**
     * Sum of amount from payments to customer balance,
     * if positive then deposit, otherwise debt
     * @param null | OrderPayment[] $orderPayments
     * @param integer $price
     * @return integer
     */
    final protected function getPaymentDifference($orderPayments, $price)
    {
        $total = 0;
        foreach ((array) $orderPayments as $orderPayment) {
            $total += $orderPayment->amount;
        }
        return $total - $price;
    }

    /**
     * @param CustomerData $customerData
     */
    private static function guardCustomerData(CustomerData $customerData)
    {
        if (empty($customerData->name)) {
            throw new \DomainException('Customer name not set');
        }
    }

    /**
     * @param array $categories
     * @return CustomerCategory[]
     */
    private function getCategories(array $categories = null)
    {
        if (!$categories) {
            return [];
        }

        return array_map(function (int $category_id) {
            return $this->companyCustomerCategoryRepository->find($category_id);
        }, $categories);
    }
}