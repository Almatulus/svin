<?php

namespace core\services\order;

use core\helpers\order\OrderConstants;
use core\models\customer\CompanyCustomer;
use core\models\division\DivisionServiceProduct;
use core\models\order\{
    Order, OrderPayment, OrderProduct, OrderService
};
use core\repositories\CompanyCashflowRepository;
use core\repositories\CompanyCashRepository;
use core\repositories\customer\CompanyCustomerCategoryRepository;
use core\repositories\customer\CompanyCustomerRepository;
use core\repositories\CustomerRepository;
use core\repositories\division\DivisionPaymentRepository;
use core\repositories\division\DivisionRepository;
use core\repositories\DivisionServiceRepository;
use core\repositories\order\{
    OrderPaymentRepository, OrderProductRepository, OrderRepository, OrderServiceRepository
};
use core\repositories\PaymentRepository;
use core\repositories\StaffRepository;
use core\repositories\warehouse\ProductRepository;
use core\repositories\warehouse\UsageRepository;
use core\services\dto\CustomerData;
use core\services\order\dto\OrderData;
use core\services\order\dto\OrderServiceData;
use core\services\TransactionManager;

class OrderApiService extends OrderServiceAbstract
{
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
    ) {
        parent::__construct(
            $orderRepository,
            $companyCustomerRepository,
            $companyCustomerCategoryRepository,
            $customerRepository,
            $orderProductRepository,
            $orderServiceRepository,
            $orderPaymentRepository,
            $divisionServiceRepository,
            $productRepository,
            $paymentRepository,
            $staffReviewRepository,
            $companyCashRepository,
            $companyCashflowRepository,
            $usageRepository,
            $divisionPaymentRepository,
            $divisionRepository,
            $transactionManager
        );
    }

    /**
     * @param OrderData          $orderData
     * @param OrderServiceData[] $servicesData
     * @param CustomerData       $customerData
     *
     * @return Order
     * @throws \Exception
     */
    public function create(OrderData $orderData, $servicesData, CustomerData $customerData)
    {
        $staff = $this->staffRepository->find($orderData->staff_id);
        $division = $this->divisionRepository->find($orderData->division_id);
        $companyCustomer = $this->getCompanyCustomer($customerData, $division->company_id);
        $companyCash = $this->companyCashRepository->findFirst($division->company_id);

        $order = Order::add(
            $companyCustomer,
            $division,
            $staff,
            $companyCash,
            OrderConstants::TYPE_APPLICATION,
            $orderData->created_user_id,
            $orderData->getDatetime(),
            $orderData->note,
            $orderData->notify_hours_before,
            $orderData->color,
            null,
            null
        );

        $orderServices = $this->getOrderServices($servicesData, $order, $companyCustomer);
        $orderProducts = $this->getOrderProducts($orderServices);

        $order->editDuration($this->getCalculatedDuration($orderServices));
        $order->editPrice(
            $this->getCalculatedPrice(
                $orderServices,
                $orderProducts
            )
        );

        $orderPayment = null;
        $divisionPayments = $this->divisionPaymentRepository->findAllByDivision($division->id);
        if (!empty($divisionPayments)) {
            // First payment
            $payment = reset($divisionPayments)->payment;
            $orderPayment = OrderPayment::add($order, $payment, $order->price);
        }
        $order->editPaymentDifference(
            $this->getPaymentDifference([$orderPayment], $order->price)
        );

        $order->setPayments([$orderPayment]);
        $order->setProducts($orderProducts);
        $order->setServices($orderServices);

        $this->transactionManager->execute(function () use ($order) {
            $this->orderRepository->save($order);

            $order->trigger(Order::EVENT_INSERT);
        });

        return $order;
    }

    /**
     * Returns new OrderService models
     * @param OrderServiceData[] $servicesData
     * @param Order $order
     * @param CompanyCustomer $companyCustomer
     * @return OrderService[]
     */
    private function getOrderServices($servicesData, Order $order, CompanyCustomer $companyCustomer)
    {
        return array_map(function (OrderServiceData $data) use ($order, $companyCustomer) {
            $divisionService = $this->divisionServiceRepository->find($data->division_service_id);
            return OrderService::add(
                $order,
                $divisionService,
                $companyCustomer->discount,
                $divisionService->average_time,
                $divisionService->price,
                $data->quantity
            );
        }, $servicesData);
    }

    /**
     * Returns new OrderProduct models
     * @param OrderService[] $ordersServices
     * @return OrderProduct[]
     */
    private function getOrderProducts($ordersServices)
    {
        return array_reduce($ordersServices, function ($orderProducts, OrderService $orderService) {
            /* @var DivisionServiceProduct[] products */
            $divisionServiceProducts = $orderService->divisionService->products;
            return array_merge($orderProducts, array_map(function (DivisionServiceProduct $divisionServiceProduct) use ($orderService) {
                $product = $this->productRepository->find($divisionServiceProduct->product_id);
                return OrderProduct::add(
                    $orderService->order,
                    $product,
                    $divisionServiceProduct->quantity,
                    $product->purchase_price,
                    $product->price
                );
            }, $divisionServiceProducts));
        }, []);
    }
}
