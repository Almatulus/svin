<?php

namespace modules\order\services;

use Codeception\Specify;
use Codeception\Test\Unit;
use core\helpers\company\CashbackHelper;
use core\helpers\company\PaymentHelper;
use core\helpers\division\ServiceHelper;
use core\helpers\GenderHelper;
use core\helpers\order\OrderConstants;
use core\models\company\Cashback;
use core\models\customer\CompanyCustomer;
use core\models\customer\Customer;
use core\models\customer\CustomerCategory;
use core\models\customer\CustomerRequestTemplate;
use core\models\customer\DelayedNotification;
use core\models\division\Division;
use core\models\division\DivisionService;
use core\models\finance\CompanyCash;
use core\models\finance\CompanyCashflow;
use core\models\finance\CompanyCashflowProduct;
use core\models\finance\CompanyCashflowService;
use core\models\finance\CompanyCostItem;
use core\models\InsuranceCompany;
use core\models\order\Order;
use core\models\order\OrderPayment;
use core\models\order\OrderProduct;
use core\models\order\OrderService;
use core\models\Payment;
use core\models\Staff;
use core\models\StaffDivisionMap;
use core\models\user\User;
use core\models\warehouse\Product;
use core\models\warehouse\Usage;
use core\repositories\exceptions\NotFoundException;
use core\services\{
    dto\CustomerData
};
use core\services\order\{
    dto\OrderContactData, dto\OrderData, dto\OrderPaymentData, dto\OrderServiceData, dto\ProductData, OrderModelService
};

class OrderModelServiceTest extends Unit
{
    use Specify;

    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * @var OrderModelService $orderService
     */
    private $orderService;

    /** @var CompanyCash */
    private $companyCash;
    /** @var  CompanyCustomer */
    private $companyCustomer;
    /** @var CustomerCategory[] */
    private $categories;
    /** @var  CompanyCustomer */
    private $contact;
    /** @var  Division */
    private $division;
    /** @var DivisionService */
    private $divisionService;
    /** @var InsuranceCompany */
    private $insuranceCompany;
    /** @var Staff */
    private $staff;

    /**
     * @var Product[]
     */
    private $products;
    /**
     * @var Payment[]
     */
    private $payments;

    /**
     * @var User
     */
    private $user;

    protected function _before()
    {
        $this->orderService = \Yii::createObject(['class' => OrderModelService::class]);

        $this->user = $this->tester->login();
        $this->companyCustomer = $this->tester->getFactory()->create(CompanyCustomer::class,
            ['company_id' => $this->user->company_id]);
        $this->contact = $this->tester->getFactory()->create(CompanyCustomer::class,
            ['company_id' => $this->user->company_id]);
        $this->division = $this->tester->getFactory()->create(Division::class,
            ['company_id' => $this->user->company_id]);
        $this->companyCash = $this->tester->getFactory()->create(CompanyCash::class,
            ['company_id' => $this->user->company_id, 'division_id' => $this->division->id]);
        $this->divisionService = $this->tester->getFactory()->create(DivisionService::class);
        $this->divisionService->link('divisions', $this->division);
        $this->staff = $this->tester->getFactory()->create(Staff::class);
        $this->staff->link('divisionServices', $this->divisionService);

        $this->tester->getFactory()->create(StaffDivisionMap::class, [
            'division_id' => $this->division->id,
            'staff_id'    => $this->staff->id,
        ]);
        $this->products = $this->tester->getFactory()->seed(2, Product::class,
            [
                'company_id'  => $this->user->company_id,
                'division_id' => $this->division->id,
                'quantity'    => $this->tester->getFaker()->numberBetween(100, 1000)
            ]);
        $this->payments = $this->tester->getFactory()->seed(2, Payment::class);
        $this->categories = $this->tester->getFactory()->seed(2, CustomerCategory::class, [
            'company_id' => $this->user->company_id
        ]);
        $this->insuranceCompany = $this->tester->getFactory()->create(InsuranceCompany::class);
    }

    protected function _after()
    {
    }

    /**
     * ToDo Should Be Handled
     */
    public function testCreateWithEmptyDatetime()
    {
        $this->markAsRisky();
        $this->expectException(\Error::class);

        $orderData = $this->getOrderData();
        $orderData->datetime = null;
        $this->orderService->create(
            $orderData,
            $this->getOrderServiceData(),
            $this->getOrderProductData(),
            $this->getOrderPaymentData(),
            $this->getOrderContactData(),
            $this->getCustomerData()
        );
    }

    /**
     *
     */
    public function testCreateWithEmptyCustomerPhone()
    {
        $customerData = $this->getCustomerData(false, true, false);
        $order = $this->orderService->create(
            $this->getOrderData(),
            $this->getOrderServiceData(),
            $this->getOrderProductData(),
            $this->getOrderPaymentData(),
            $this->getOrderContactData(),
            $customerData
        );

        $this->tester->canSeeRecord(Order::class, ['status' => OrderConstants::STATUS_ENABLED]);
        $this->tester->canSeeRecord(Customer::class,
            ['id' => $order->companyCustomer->customer_id, 'name' => $customerData->name]);
    }

    public function testCreateWithEmptyCustomerName()
    {
        $this->expectException(\DomainException::class);

        $this->orderService->create(
            $this->getOrderData(),
            $this->getOrderServiceData(),
            $this->getOrderProductData(),
            $this->getOrderPaymentData(),
            $this->getOrderContactData(),
            $this->getCustomerData(false, false)
        );
    }

    /**
     * ToDo Should Be Handled
     */
    public function testCreateWithEmptyServices()
    {
        $this->markAsRisky();
        $this->orderService->create(
            $this->getOrderData(),
            [],
            $this->getOrderProductData(),
            $this->getOrderPaymentData(),
            $this->getOrderContactData(),
            $this->getCustomerData()
        );

        $this->tester->canSeeRecord(Order::class, ['status' => OrderConstants::STATUS_ENABLED]);
    }

    public function testCreateWithEmptyProducts()
    {
        $this->orderService->create(
            $this->getOrderData(),
            $this->getOrderServiceData(),
            [],
            $this->getOrderPaymentData(),
            $this->getOrderContactData(),
            $this->getCustomerData()
        );

        $this->tester->canSeeRecord(Order::class, ['status' => OrderConstants::STATUS_ENABLED]);
    }

    public function testCreateWithEmptyPayments()
    {
        $this->orderService->create(
            $this->getOrderData(),
            $this->getOrderServiceData(),
            $this->getOrderProductData(),
            [],
            $this->getOrderContactData(),
            $this->getCustomerData()
        );

        $this->tester->canSeeRecord(Order::class, ['status' => OrderConstants::STATUS_ENABLED]);
    }

    public function testCreateWithEmptyStaff()
    {
        $this->expectException(NotFoundException::class);

        $orderData = $this->getOrderData();
        $orderData->staff_id = null;
        $this->orderService->create(
            $orderData,
            $this->getOrderServiceData(),
            $this->getOrderProductData(),
            $this->getOrderPaymentData(),
            $this->getOrderContactData(),
            $this->getCustomerData()
        );
    }

    public function testCreateWithEmptyDivision()
    {
        $this->expectException(NotFoundException::class);

        $orderData = $this->getOrderData();
        $orderData->division_id = null;
        $this->orderService->create(
            $orderData,
            $this->getOrderServiceData(),
            $this->getOrderProductData(),
            $this->getOrderPaymentData(),
            $this->getOrderContactData(),
            $this->getCustomerData()
        );
    }

    public function testCreateWithEmptyCash()
    {
        $this->expectException(NotFoundException::class);

        $orderData = $this->getOrderData();
        $orderData->company_cash_id = null;
        $this->orderService->create(
            $orderData,
            $this->getOrderServiceData(),
            $this->getOrderProductData(),
            $this->getOrderPaymentData(),
            $this->getOrderContactData(),
            $this->getCustomerData()
        );
    }

    public function testCreateWithWrongCash()
    {
        $this->expectException(NotFoundException::class);

        $orderData = $this->getOrderData();
        $orderData->company_cash_id = 1;
        $this->orderService->create(
            $orderData,
            $this->getOrderServiceData(),
            $this->getOrderProductData(),
            $this->getOrderPaymentData(),
            $this->getOrderContactData(),
            $this->getCustomerData()
        );
    }

    public function testCreateWithWrongStaff()
    {
        $this->expectException(NotFoundException::class);

        $orderData = $this->getOrderData();
        $orderData->staff_id = 0;
        $this->orderService->create(
            $orderData,
            $this->getOrderServiceData(),
            $this->getOrderProductData(),
            $this->getOrderPaymentData(),
            $this->getOrderContactData(),
            $this->getCustomerData()
        );
    }

    public function testCreateWithWrongDivision()
    {
        $this->expectException(NotFoundException::class);

        $orderData = $this->getOrderData();
        $orderData->division_id = 0;
        $this->orderService->create(
            $orderData,
            $this->getOrderServiceData(),
            $this->getOrderProductData(),
            $this->getOrderPaymentData(),
            $this->getOrderContactData(),
            $this->getCustomerData()
        );
    }

    /**
     * ToDo Should Be Handled
     */
    public function testCreateWithServicesNotAssignedToStaff()
    {
        $this->markAsRisky();
        $divisionService = $this->tester->getFactory()->create(DivisionService::class);
        $divisionService->link('divisions', $this->division);

        $this->orderService->create(
            $this->getOrderData(),
            [
                new OrderServiceData(
                    $divisionService->id,
                    $divisionService->price,
                    $divisionService->average_time,
                    0,
                    1
                )
            ],
            [],
            $this->getOrderPaymentData(),
            $this->getOrderContactData(),
            $this->getCustomerData()
        );
    }

    public function testCreateInsufficientCashbackBalance()
    {
        $this->expectException(\DomainException::class);

        $this->companyCustomer->cashback_balance = 1000;
        $this->companyCustomer->save(false);

        $cashbackPayment = $this->tester->getFactory()->create(Payment::class, [
            'type' => PaymentHelper::CASHBACK
        ]);

        $this->orderService->create(
            $this->getOrderData(),
            $this->getOrderServiceData(),
            $this->getOrderProductData(),
            [
                new OrderPaymentData($cashbackPayment->id, 1100)
            ],
            $this->getOrderContactData(),
            $this->getCustomerData()
        );
    }

    /**
     * @group debug
     */
    public function testCreateSuccessfullyWithNewCustomer()
    {
        $productsData = $this->getOrderProductData();
        $servicesData = $this->getOrderServiceData();
        $paymentsData = $this->getOrderPaymentData($servicesData, $productsData);

        $expectedPrice = array_reduce($servicesData, function (int $sum, OrderServiceData $serviceData) {
            return $sum + ($serviceData->price * (100 - $serviceData->discount) / 100) * $serviceData->quantity;
        }, 0);
        $expectedPrice = array_reduce($productsData, function (int $sum, ProductData $productsData) {
            $price = $productsData->selling_price;
            return $sum + $price * $productsData->quantity;
        }, $expectedPrice);
        $expectedDuration = array_reduce($servicesData, function (int $sum, OrderServiceData $serviceData) {
            return $sum + $serviceData->duration;
        }, 0);
        $exceptedPaymentDifference = array_reduce($paymentsData, function (int $sum, OrderPaymentData $paymentData) {
                return $sum + $paymentData->amount;
            }, 0) - $expectedPrice;

        $customerData = $this->getCustomerData();
        $customerData->company_customer_id = null;
        $customerData->name = $this->tester->getFaker()->firstName;

        $order = $this->orderService->create(
            $this->getOrderData(),
            $servicesData,
            $productsData,
            $paymentsData,
            $this->getOrderContactData(),
            $customerData
        );

        $this->tester->canSeeRecord(Order::class, ['status' => OrderConstants::STATUS_ENABLED]);

        verify($order->price)->equals($expectedPrice);
        verify($order->duration)->equals($expectedDuration);
        verify($order->payment_difference)->equals($exceptedPaymentDifference);

        $this->tester->canSeeRecord(Customer::class, [
            'id'         => $order->companyCustomer->customer_id,
            'gender'     => $customerData->gender,
            'birth_date' => $customerData->birth_date
        ]);

        $this->tester->canSeeRecord(CompanyCustomer::class, [
            'id'                   => $order->company_customer_id,
            'insurance_company_id' => $customerData->insurance_company_id,
            'medical_record_id'    => $customerData->medical_record_id
        ]);

        verify(array_map(function (CustomerCategory $category) {
            return $category->id;
        }, $order->companyCustomer->categories))->equals($customerData->categories);

        $this->tester->canSeeRecord(OrderService::class, [
            'order_id' => $order->id,
            'discount' => 0,
            'duration' => $this->divisionService->average_time,
            'price'    => $this->divisionService->price,
            'quantity' => 1
        ]);

        foreach ($servicesData as $serviceData) {
            $products = $productsData[$serviceData->division_service_id] ?? [];
            foreach ($products as $productData) {
                $this->tester->canSeeRecord(OrderProduct::class, [
                    'order_service_id' => $order->orderServices[0]->id,
                    'product_id'       => $productData->product_id,
                    'selling_price'    => $productData->selling_price,
                    'quantity'         => $productData->quantity
                ]);
            }
        }

        foreach ($paymentsData as $paymentData) {
            $this->tester->canSeeRecord(OrderPayment::class, [
                'order_id'   => $order->id,
                'payment_id' => $paymentData->payment_id,
                'amount'     => $paymentData->amount,
            ]);
        }
    }

    /**
     * @group debug
     */
    public function testUpdateSuccessfullyWithNewCustomer()
    {
        $productsData = $this->getOrderProductData();
        $servicesData = $this->getOrderServiceData();
        $paymentsData = $this->getOrderPaymentData($servicesData, $productsData);

        $order = $this->tester->getFactory()->create(Order::class, [
            'company_customer_id' => $this->companyCustomer->id
        ]);

        $customerData = $this->getCustomerData();
        $customerData->company_customer_id = null;
        $customerData->name = $this->tester->getFaker()->firstName;
        $customerData->birth_date = date("Y-m-d");

        $order = $this->orderService->update(
            $order->id,
            false,
            $this->getOrderData(),
            $servicesData,
            $productsData,
            $paymentsData,
            $this->getOrderContactData(),
            $customerData
        );

        $this->tester->cantSeeRecord(Order::class, [
            'id'                  => $order->id,
            'company_customer_id' => $this->companyCustomer->id
        ]);

        $this->tester->canSeeRecord(Customer::class, [
            'name'       => $customerData->name,
            'birth_date' => $customerData->birth_date
        ]);
    }

    public function testCreateSuccessfully()
    {
        $productsData = $this->getOrderProductData();
        $servicesData = $this->getOrderServiceData();
        $paymentsData = $this->getOrderPaymentData($servicesData, $productsData);

        $expectedPrice = array_reduce($servicesData, function (int $sum, OrderServiceData $serviceData) {
            return $sum + ($serviceData->price * (100 - $serviceData->discount) / 100) * $serviceData->quantity;
        }, 0);
        $expectedPrice = array_reduce($productsData, function (int $sum, ProductData $productsData) {
            $price = $productsData->selling_price;
            return $sum + $price * $productsData->quantity;
        }, $expectedPrice);
        $expectedDuration = array_reduce($servicesData, function (int $sum, OrderServiceData $serviceData) {
            return $sum + $serviceData->duration;
        }, 0);
        $exceptedPaymentDifference = array_reduce($paymentsData, function (int $sum, OrderPaymentData $paymentData) {
                return $sum + $paymentData->amount;
            }, 0) - $expectedPrice;

        $customerData = $this->getCustomerData();
        $order = $this->orderService->create(
            $this->getOrderData(),
            $servicesData,
            $productsData,
            $paymentsData,
            $this->getOrderContactData(),
            $customerData
        );

        $this->tester->canSeeRecord(Order::class, ['status' => OrderConstants::STATUS_ENABLED]);

        verify($order->price)->equals($expectedPrice);
        verify($order->duration)->equals($expectedDuration);
        verify($order->payment_difference)->equals($exceptedPaymentDifference);

        $this->tester->canSeeRecord(Customer::class, [
            'id'         => $order->companyCustomer->customer_id,
            'gender'     => $customerData->gender,
            'birth_date' => $customerData->birth_date
        ]);

        $this->tester->canSeeRecord(CompanyCustomer::class, [
            'id'                   => $customerData->company_customer_id,
            'insurance_company_id' => $customerData->insurance_company_id
        ]);

        verify(array_map(function (CustomerCategory $category) {
            return $category->id;
        }, $order->companyCustomer->categories))->equals($customerData->categories);

        $this->tester->canSeeRecord(OrderService::class, [
            'order_id' => $order->id,
            'discount' => 0,
            'duration' => $this->divisionService->average_time,
            'price'    => $this->divisionService->price,
            'quantity' => 1
        ]);

        foreach ($servicesData as $serviceData) {
            $products = $productsData[$serviceData->division_service_id] ?? [];
            foreach ($products as $productData) {
                $this->tester->canSeeRecord(OrderProduct::class, [
                    'order_service_id' => $order->orderServices[0]->id,
                    'product_id'       => $productData->product_id,
                    'selling_price'    => $productData->selling_price,
                    'quantity'         => $productData->quantity
                ]);
            }
        }

        foreach ($paymentsData as $paymentData) {
            $this->tester->canSeeRecord(OrderPayment::class, [
                'order_id'   => $order->id,
                'payment_id' => $paymentData->payment_id,
                'amount'     => $paymentData->amount,
            ]);
        }
    }

    /**
     * @group debug
     */
    public function testUpdate()
    {
        $this->companyCustomer->updateAttributes(['balance' => 0]);

        $order = $this->tester->getFactory()->create(Order::class, [
            'company_customer_id' => $this->companyCustomer->id,
            'company_cash_id'     => $this->companyCash->id,
            'division_id'         => $this->division->id,
            'staff_id'            => $this->staff->id,
            'status'              => OrderConstants::STATUS_ENABLED
        ]);

        $paymentsData = $this->getOrderPaymentData();
        $productsData = $this->getOrderProductData();
        $servicesData = $this->getOrderServiceData();

        $expectedPrice = array_reduce($servicesData, function (int $sum, OrderServiceData $serviceData) {
            return $sum + ($serviceData->price * (100 - $serviceData->discount) / 100) * $serviceData->quantity;
        }, 0);
        $expectedPrice = array_reduce($productsData, function (int $sum, ProductData $productsData) {
            return $sum + $productsData->selling_price * $productsData->quantity;
        }, $expectedPrice);
        $expectedDuration = array_reduce($servicesData, function (int $sum, OrderServiceData $serviceData) {
            return $sum + $serviceData->duration;
        }, 0);
        $exceptedPaymentDifference = array_reduce($paymentsData, function (int $sum, OrderPaymentData $paymentData) {
                return $sum + $paymentData->amount;
            }, 0) - $expectedPrice;

        $order = $this->orderService->update(
            $order->id,
            false,
            $this->getOrderData(),
            $servicesData,
            $productsData,
            $paymentsData,
            $this->getOrderContactData(),
            $this->getCustomerData()
        );

        $this->tester->canSeeRecord(Order::class, ['status' => OrderConstants::STATUS_ENABLED]);

        verify($order->price)->equals($expectedPrice);
        verify($order->duration)->equals($expectedDuration);
        verify($order->payment_difference)->equals($exceptedPaymentDifference);

        $this->tester->canSeeRecord(OrderService::class, [
            'order_id' => $order->id,
            'discount' => 0,
            'duration' => $this->divisionService->average_time,
            'price'    => $this->divisionService->price,
            'quantity' => 1
        ]);

        $this->tester->canSeeRecord(OrderProduct::class, [
            'order_id'      => $order->id,
            'product_id'    => $productsData[0]->product_id,
            'selling_price' => $productsData[0]->selling_price,
            'quantity'      => $productsData[0]->quantity
        ]);

        $this->tester->canSeeRecord(OrderProduct::class, [
            'order_id'      => $order->id,
            'product_id'    => $productsData[1]->product_id,
            'selling_price' => $productsData[1]->selling_price,
            'quantity'      => $productsData[1]->quantity
        ]);

        $this->tester->canSeeRecord(OrderPayment::class, [
            'order_id'   => $order->id,
            'payment_id' => $paymentsData[0]->payment_id,
            'amount'     => $paymentsData[0]->amount,
        ]);

        $this->tester->canSeeRecord(OrderPayment::class, [
            'order_id'   => $order->id,
            'payment_id' => $paymentsData[1]->payment_id,
            'amount'     => $paymentsData[1]->amount,
        ]);
    }

    /**
     * @group duration
     */
    public function testUpdateDuration()
    {
        $order = $this->tester->getFactory()->create(Order::class, [
            'company_customer_id' => $this->companyCustomer->id,
            'company_cash_id'     => $this->companyCash->id,
            'division_id'         => $this->division->id,
            'staff_id'            => $this->staff->id,
            'status'              => OrderConstants::STATUS_ENABLED
        ]);

        $firstServiceDuration = 30;
        $firstOrderService = $this->tester->getFactory()->create(OrderService::class, [
            'order_id'            => $order->id,
            'division_service_id' => $this->divisionService->id,
            'duration'            => $firstServiceDuration,
            'price'               => $this->divisionService->price
        ]);
        $secondServiceDuration = 30;
        $secondOrderService = $this->tester->getFactory()->create(OrderService::class, [
            'order_id' => $order->id,
            'duration' => $secondServiceDuration
        ]);
        $order->duration = $firstOrderService->duration + $secondOrderService->duration;
        $order->price = $firstOrderService->getSalePrice() + $secondOrderService->getSalePrice();
        $order->update(false);

        $delta = 50;
        $oldDuration = $order->duration;
        $newDuration = $oldDuration + $delta;

        $order = $this->orderService->updateDuration($order->id, $newDuration);

        verify($order->duration)->equals($newDuration);
        $this->tester->canSeeRecord(OrderService::class, [
            'order_id' => $order->id,
            'duration' => $firstServiceDuration + $delta
        ]);
        $this->tester->canSeeRecord(OrderService::class, [
            'order_id' => $order->id,
            'duration' => $secondServiceDuration
        ]);

        $newDuration = $oldDuration - $delta;

        $order = $this->orderService->updateDuration($order->id, $newDuration);

        verify($order->duration)->equals($newDuration);
        $this->tester->canSeeRecord(OrderService::class, [
            'order_id' => $order->id,
            'duration' => $firstServiceDuration + $secondServiceDuration - $delta
        ]);
        $this->tester->canSeeRecord(OrderService::class, [
            'order_id' => $order->id,
            'duration' => 0
        ]);
    }

    public function testMove()
    {
        $order = $this->tester->getFactory()->create(Order::class, [
            'company_customer_id' => $this->companyCustomer->id,
            'company_cash_id'     => $this->companyCash->id,
            'division_id'         => $this->division->id,
            'staff_id'            => $this->staff->id,
            'status'              => OrderConstants::STATUS_ENABLED
        ]);
        $newDatetime = (new \DateTime($order->datetime))->modify("+5 days 12 hours")->format("Y-m-d H:i:s");

        $order = $this->orderService->move($order->id, $order->staff_id, $newDatetime);
        verify($order->staff_id)->equals($this->staff->id);
        verify($order->datetime)->equals($newDatetime);

        $newStaff = $this->tester->getFactory()->create(Staff::class);
        $order = $this->orderService->move($order->id, $newStaff->id, $newDatetime);
        verify($order->datetime)->equals($newDatetime);
        verify($order->staff_id)->equals($newStaff->id);
    }

    public function testDisable()
    {
        $order = $this->tester->getFactory()->create(Order::class, [
            'company_customer_id' => $this->companyCustomer->id,
            'company_cash_id'     => $this->companyCash->id,
            'division_id'         => $this->division->id,
            'staff_id'            => $this->staff->id,
            'status'              => OrderConstants::STATUS_ENABLED
        ]);
        $order = $this->orderService->disable($order->id);
        verify($order->status)->equals(OrderConstants::STATUS_DISABLED);
    }

    public function testEnable()
    {
        $order = $this->tester->getFactory()->create(Order::class, [
            'company_customer_id' => $this->companyCustomer->id,
            'company_cash_id'     => $this->companyCash->id,
            'division_id'         => $this->division->id,
            'staff_id'            => $this->staff->id,
            'status'              => OrderConstants::STATUS_CANCELED
        ]);
        $order = $this->orderService->enable($order->id);
        verify($order->status)->equals(OrderConstants::STATUS_ENABLED);
    }

    public function testCancel()
    {
        $order = $this->tester->getFactory()->create(Order::class, [
            'company_customer_id' => $this->companyCustomer->id,
            'company_cash_id'     => $this->companyCash->id,
            'division_id'         => $this->division->id,
            'staff_id'            => $this->staff->id,
            'status'              => OrderConstants::STATUS_ENABLED
        ]);
        $order = $this->orderService->cancel($order->id);
        verify($order->status)->equals(OrderConstants::STATUS_CANCELED);
    }

    /**
     * @group debug
     * @param $status
     * @param $exception
     * @dataProvider checkoutProvider
     */
    public function testCheckout($status, $exception)
    {
        if ($exception) {
            $this->expectException($exception);
        }

        $newStaff = $this->tester->getFactory()->create(Staff::class);

        $order = $this->tester->getFactory()->create(Order::class, [
            'company_cash_id'     => $this->companyCash->id,
            'company_customer_id' => $this->companyCustomer->id,
            'created_user_id'     => $this->user->id,
            'division_id'         => $this->division->id,
            'staff_id'            => $this->staff->id,
            'status'              => $status
        ]);
        $orderData = $this->getOrderData($order);
        $orderData->staff_id = $newStaff->id;

        $paymentsData = $this->getOrderPaymentData();
        $servicesData = $this->getOrderServiceData();
        $productsData = $this->getOrderProductData();

        $this->orderService->checkout(
            $order->id,
            false,
            $orderData,
            $servicesData,
            $productsData,
            $paymentsData,
            $this->getOrderContactData(),
            $this->getCustomerData()
        );

        $this->tester->canSeeRecord(Order::class, [
            'staff_id' => $newStaff->id,
            'status'   => OrderConstants::STATUS_FINISHED
        ]);

        $this->tester->canSeeRecord(OrderService::class, [
            'order_id' => $order->id,
            'discount' => 0,
            'duration' => $this->divisionService->average_time,
            'price'    => $this->divisionService->price,
            'quantity' => 1
        ]);

        $this->tester->canSeeRecord(CompanyCashflowService::class, [
            'service_id' => $order->orderServices[0]->division_service_id,
            'price'      => $order->orderServices[0]->price,
            'discount'   => $order->orderServices[0]->discount,
            'quantity'   => $order->orderServices[0]->quantity,
        ]);

        foreach ($servicesData as $serviceData) {
            $products = $productsData[$serviceData->division_service_id] ?? [];
            foreach ($products as $key => $productData) {
                $this->tester->canSeeRecord(OrderProduct::class, [
                    'order_service_id' => $order->orderServices[0]->id,
                    'product_id'       => $productData->product_id,
                    'selling_price'    => $productData->selling_price,
                    'quantity'         => $productData->quantity
                ]);

                $this->tester->canSeeRecord(CompanyCashflowProduct::class, [
                    'product_id' => $order->orderServices[0]->products[$key]->product_id,
                    'price'      => $order->orderServices[0]->price,
                    'quantity'   => $order->orderServices[0]->quantity,
                ]);
            }
        }

        foreach ($paymentsData as $paymentData) {
            $this->tester->canSeeRecord(OrderPayment::class, [
                'order_id'   => $order->id,
                'payment_id' => $paymentData->payment_id,
                'amount'     => $paymentData->amount,
            ]);
        }

        $this->tester->canSeeRecord(Usage::class, [
            'company_customer_id' => $this->companyCustomer->id,
            'division_id'         => $this->division->id,
            'staff_id'            => $orderData->staff_id
        ]);
    }

    // ToDo move to separate class to test notification service
    public function testCreateDelayedNotificationsAfterCheckout()
    {
        $this->tester->getFactory()->create(CustomerRequestTemplate::class, [
            'company_id' => $this->user->company_id,
            'is_enabled' => true,
            'key'        => strval(CustomerRequestTemplate::TYPE_NOTIFY_HEALTH_EXAMINATION)
        ]);

        $divisionService = $this->tester->getFactory()->create(DivisionService::class, [
            'notification_delay' => ServiceHelper::TWO_WEEKS
        ]);
        $divisionService->link('divisions', $this->division);

        $order = $this->tester->getFactory()->create(Order::class, [
            'company_cash_id'     => $this->companyCash->id,
            'company_customer_id' => $this->companyCustomer->id,
            'created_user_id'     => $this->user->id,
            'division_id'         => $this->division->id,
            'staff_id'            => $this->staff->id,
            'status'              => OrderConstants::STATUS_ENABLED
        ]);
        $paymentsData = $this->getOrderPaymentData();
        $servicesData = $this->getOrderServiceData();
        $servicesData[0]->division_service_id = $divisionService->id;
        $productsData = $this->getOrderProductData();

        $order = $this->orderService->checkout(
            $order->id,
            false,
            $this->getOrderData($order),
            $servicesData,
            $productsData,
            $paymentsData,
            $this->getOrderContactData(),
            $this->getCustomerData()
        );

        $this->tester->canSeeRecord(DelayedNotification::class, [
            'company_customer_id' => $this->companyCustomer->id,
            'division_service_id' => $divisionService->id,
            'date'                => (new \DateTime($order->datetime))->modify("+{$divisionService->getDelay()}")->format("Y-m-d"),
            'interval'            => $divisionService->getDelay(),
            'status'              => DelayedNotification::STATUS_NEW
        ]);
    }

    // ToDo move to separate class to test notification service
    public function testRemoveDelayedNotificationsAfterReset()
    {
        $order = $this->tester->getFactory()->create(Order::class, [
            'company_cash_id'     => $this->companyCash->id,
            'company_customer_id' => $this->companyCustomer->id,
            'created_user_id'     => $this->user->id,
            'division_id'         => $this->division->id,
            'staff_id'            => $this->staff->id,
            'status'              => OrderConstants::STATUS_FINISHED
        ]);
        $divisionService = $this->tester->getFactory()->create(DivisionService::class, [
            'notification_delay' => ServiceHelper::TWO_WEEKS
        ]);
        $divisionService->link('divisions', $this->division);
        $orderService = $this->tester->getFactory()->create(OrderService::class, [
            'order_id'            => $order->id,
            'division_service_id' => $divisionService->id
        ]);

        $delayedNotification = $this->tester->getFactory()->create(DelayedNotification::class, [
            'interval'            => $divisionService->getDelay(),
            'date'                => (new \DateTime($order->datetime))->modify("+{$divisionService->getDelay()}")->format("Y-m-d"),
            'status'              => DelayedNotification::STATUS_NEW,
            'company_customer_id' => $this->companyCustomer->id,
            'division_service_id' => $divisionService->id
        ]);

        $this->orderService->reset($order->id);

        $this->tester->canSeeRecord(DelayedNotification::class, [
            'id'     => $delayedNotification->id,
            'status' => DelayedNotification::STATUS_CANCELED
        ]);
    }

    /**
     * @group cashback
     * @throws \Exception
     */
    public function testChargeCashback()
    {
        $cashbackBalance = 1000;
        $cashbackPercent = 10;
        $this->companyCustomer->updateAttributes([
            'cashback_balance' => $cashbackBalance,
            'cashback_percent' => $cashbackPercent
        ]);

        $order = $this->tester->getFactory()->create(Order::class, [
            'company_cash_id'     => $this->companyCash->id,
            'company_customer_id' => $this->companyCustomer->id,
            'created_user_id'     => $this->user->id,
            'division_id'         => $this->division->id,
            'staff_id'            => $this->staff->id,
            'status'              => OrderConstants::STATUS_ENABLED
        ]);
        $this->payments[0]->id = PaymentHelper::CASH_ID;
        $this->payments[1]->id = PaymentHelper::CARD_ID;

        $servicesData = $this->getOrderServiceData();
        $paymentsData = $this->getOrderPaymentData();
        $productsData = $this->getOrderProductData();

        $order = $this->orderService->checkout(
            $order->id,
            false,
            $this->getOrderData($order),
            $servicesData,
            $productsData,
            $paymentsData,
            $this->getOrderContactData(),
            $this->getCustomerData()
        );

        $incomeCashback = intval(array_reduce($paymentsData, function (int $sum, OrderPaymentData $data) {
                if ($data->payment_id == PaymentHelper::CASH_ID || $data->payment_id == PaymentHelper::CARD_ID) {
                    return $sum + $data->amount;
                }
                return $sum;
            }, 0) * $cashbackPercent / 100);

        $this->tester->canSeeRecord(Cashback::class, [
            'company_customer_id' => $this->companyCustomer->id,
            'type'                => CashbackHelper::TYPE_IN,
            'amount'              => $incomeCashback,
            'percent'             => $cashbackPercent
        ]);

        $this->tester->canSeeRecord(CompanyCustomer::class, [
            'id'               => $this->companyCustomer->id,
            'cashback_balance' => $cashbackBalance + $incomeCashback
        ]);

    }

    /**
     * @group cashback
     */
    public function testSubtractCashback()
    {
        $cashbackPercent = 10;
        $cashbackBalance = 1000;
        $this->companyCustomer->updateAttributes([
            'cashback_percent' => $cashbackPercent,
            'cashback_balance' => $cashbackBalance
        ]);

        $order = $this->tester->getFactory()->create(Order::class, [
            'company_cash_id'     => $this->companyCash->id,
            'company_customer_id' => $this->companyCustomer->id,
            'created_user_id'     => $this->user->id,
            'division_id'         => $this->division->id,
            'staff_id'            => $this->staff->id,
            'status'              => OrderConstants::STATUS_ENABLED,
        ]);
        $cashbackPayment = $this->tester->getFactory()->create(Payment::class, [
            'type' => PaymentHelper::CASHBACK
        ]);

        $cashbackPaymentAmount = 500;
        $servicesData = $this->getOrderServiceData();
        $paymentsData = [new OrderPaymentData($cashbackPayment->id, $cashbackPaymentAmount)];
        $productsData = $this->getOrderProductData();

        $order = $this->orderService->checkout(
            $order->id,
            false,
            $this->getOrderData($order),
            $servicesData,
            $productsData,
            $paymentsData,
            $this->getOrderContactData(),
            $this->getCustomerData()
        );

        $expectedCashbackBalance = $cashbackBalance - $cashbackPaymentAmount;

        $this->tester->canSeeRecord(Cashback::class, [
            'company_customer_id' => $this->companyCustomer->id,
            'type'                => CashbackHelper::TYPE_OUT,
            'amount'              => $cashbackPaymentAmount,
            'percent'             => $cashbackPercent
        ]);

        $this->tester->canSeeRecord(CompanyCustomer::class, [
            'id'               => $this->companyCustomer->id,
            'cashback_balance' => $expectedCashbackBalance
        ]);
    }

    /**
     * @group insurance
     * @throws \Exception
     */
    public function testInsurancePayment()
    {
        $order = $this->tester->getFactory()->create(Order::class, [
            'datetime'            => date("Y-m-d H:i:00"),
            'company_cash_id'     => $this->companyCash->id,
            'company_customer_id' => $this->companyCustomer->id,
            'created_user_id'     => $this->user->id,
            'division_id'         => $this->division->id,
            'staff_id'            => $this->staff->id,
            'status'              => OrderConstants::STATUS_ENABLED
        ]);
        $this->payments[1] = $this->tester->getFactory()->create( Payment::class, [
            'type' => PaymentHelper::INSURANCE
        ]);
        $servicesData = $this->getOrderServiceData();
        $productsData = $this->getOrderProductData();
        $paymentsData = $this->getOrderPaymentData($servicesData, $productsData);

        $order = $this->orderService->checkout(
            $order->id,
            false,
            $this->getOrderData($order),
            $servicesData,
            $productsData,
            $paymentsData,
            $this->getOrderContactData(),
            $this->getCustomerData()
        );

        $this->tester->canSeeRecord(Order::class, ['status' => OrderConstants::STATUS_FINISHED]);

        foreach ($paymentsData as $paymentData) {
            $this->tester->canSeeRecord(OrderPayment::class, [
                'order_id'   => $order->id,
                'payment_id' => $paymentData->payment_id,
                'amount'     => $paymentData->amount,
            ]);
        }

        $this->tester->canSeeRecord(CompanyCashflow::class, [
            'date'        => $order->datetime,
            'cash_id'     => $order->company_cash_id,
            'customer_id' => $order->company_customer_id,
            'staff_id'    => $order->staff_id,
            'value'       => $order->price - $paymentsData[1]->amount,
            'status'      => CompanyCashflow::STATUS_ACTIVE,
            'division_id' => $order->division_id,
        ]);

    }


    /**
     * @group payments
     */
    public function testOverpaymentWithNotAccountablePayment()
    {
        $this->expectException(\DomainException::class);

        $this->payments[0] = $this->tester->getFactory()->create(Payment::class, [
            'type' => PaymentHelper::INSURANCE
        ]);

        $servicesData = $this->getOrderServiceData();
        $paymentsData = $this->getOrderPaymentData($servicesData);
        $paymentsData[0]->amount = $paymentsData[0]->amount + 1;

        $customerData = $this->getCustomerData();
        $customerData->company_customer_id = null;

        $this->orderService->create(
            $this->getOrderData(),
            $servicesData,
            [],
            $paymentsData,
            $this->getOrderContactData(),
            $customerData
        );
    }

    public function checkoutProvider()
    {
        return [
            [OrderConstants::STATUS_ENABLED, null],
            [OrderConstants::STATUS_FINISHED, \Exception::class],
            [OrderConstants::STATUS_CANCELED, \Exception::class],
            [OrderConstants::STATUS_WAITING, \Exception::class],
            [OrderConstants::STATUS_DISABLED, \Exception::class],
        ];
    }

    /**
     * @param Order|null $order
     * @return OrderData
     */
    public function getOrderData(Order $order = null): OrderData
    {
        return new OrderData(
            $order ? new \DateTime($order->datetime) : new \DateTime(),
            $order ? $order->division_id : $this->division->id,
            $order ? $order->staff_id : $this->staff->id,
            $order ? $order->note : $this->tester->getFaker()->text(),
            $order ? $order->hours_before : 0,
            $order ? $order->color : null,
            $order ? $order->company_cash_id : $this->companyCash->id,
            $order ? $order->created_user_id : $this->user->id,
            $order ? $order->division->company_id : $this->division->company_id,
            $order ? $order->insurance_company_id : null,
            $order ? $order->referrer_id : null
        );
    }

    /**
     * @return OrderServiceData[]
     */
    public function getOrderServiceData(): array
    {
        return [
            new OrderServiceData(
                $this->divisionService->id,
                $this->divisionService->price,
                $this->divisionService->average_time,
                0,
                1
            )
        ];
    }

    /**
     * @return ProductData[][]
     */
    public function getOrderProductData(): array
    {
        return [
            new ProductData(
                $this->products[0]->id,
                $this->tester->getFaker()->numberBetween(1, 1),
                $this->products[0]->price
            ),
            new ProductData(
                $this->products[1]->id,
                $this->tester->getFaker()->numberBetween(1, 1),
                $this->products[1]->price
            )
        ];
    }

    /**
     * @param array $orderServiceData
     * @param array $orderProductData
     * @return OrderPaymentData[]
     */
    public function getOrderPaymentData(array $orderServiceData = null, array $orderProductData = null): array
    {
        if ($orderServiceData == null) {
            $firstPaymentAmount = intval($this->divisionService->price / 2);
        } else {

            $firstPaymentAmount = array_reduce($orderServiceData, function (int $sum, OrderServiceData $serviceData) {
                return $sum + $serviceData->price * (100 - $serviceData->discount) / 100;
            }, 0);
        }

        if ($orderProductData == null) {
            $secondPaymentAmount = $this->divisionService->price - $firstPaymentAmount;
        } else {
            $secondPaymentAmount = array_reduce($orderProductData, function (int $sum, ProductData $productData) {
                return $sum + $productData->selling_price * $productData->quantity;
            }, 0);
        }

        return [
            new OrderPaymentData($this->payments[0]->id, $firstPaymentAmount),
            new OrderPaymentData($this->payments[1]->id, $secondPaymentAmount),
        ];
    }

    /**
     * @return OrderContactData[]
     */
    public function getOrderContactData(): array
    {
        return [
            new OrderContactData(
                $this->contact->id,
                $this->tester->getFaker()->name,
                $this->tester->getFaker()->regexify("\+7 \d{3} \d{3} \d{2} \d{2}")
            )
        ];
    }

    /**
     * @param bool $customer_id
     * @param bool $name
     * @param bool $phone
     * @return CustomerData
     */
    private function getCustomerData($customer_id = true, $name = true, $phone = true)
    {
        return new CustomerData(
            $customer_id ? $this->companyCustomer->id : null,
            $name ? ($customer_id ? $this->companyCustomer->customer->name : $this->tester->getFaker()->name) : null,
            null,
            null,
            $phone ? $this->companyCustomer->customer->phone : null,
            $this->companyCustomer->source_id,
            $this->tester->getFaker()->word,
            (new \DateTime())->modify("-30 years")->format("Y-m-d"),
            GenderHelper::GENDER_FEMALE,
            $this->insuranceCompany->id,
            [$this->categories[0]->id, $this->categories[1]->id]
        );
    }

    /**
     * @group debt
     */
    public function testPayDebt()
    {
        $companyCustomerWithDebt = $this->tester->getFactory()->create(CompanyCustomer::class, [
            'balance'    => -9000,
            'company_id' => $this->user->company_id
        ]);

        $orderWithDebt1 = $this->tester->getFactory()->create(Order::class, [
            'company_customer_id' => $companyCustomerWithDebt->id,
            'division_id'         => $this->division->id,
            'staff_id'            => $this->staff->id,
            'price'               => 5000,
            'payment_difference'  => -5000,
            'status'              => OrderConstants::STATUS_FINISHED
        ]);
        $orderWithDebt2 = $this->tester->getFactory()->create(Order::class, [
            'company_customer_id' => $companyCustomerWithDebt->id,
            'division_id'         => $this->division->id,
            'staff_id'            => $this->staff->id,
            'price'               => 4000,
            'payment_difference'  => -4000,
            'status'              => OrderConstants::STATUS_FINISHED
        ]);

        $order = $this->tester->getFactory()->create(Order::class, [
            'price'               => $this->divisionService->price + $this->products[0]->price + $this->products[1]->price,
            'company_cash_id'     => $this->companyCash->id,
            'company_customer_id' => $companyCustomerWithDebt->id,
            'created_user_id'     => $this->user->id,
            'division_id'         => $this->division->id,
            'staff_id'            => $this->staff->id,
            'status'              => OrderConstants::STATUS_ENABLED,
            'payment_difference'  => 9000
        ]);

        $servicesData = $this->getOrderServiceData();
        $productsData = $this->getOrderProductData();
        $paymentsData = $this->getOrderPaymentData($servicesData, $productsData);
        $paymentsData[0]->amount += 5000;
        $paymentsData[1]->amount += 4000;

        $customerData = $this->getCustomerData();
        $customerData->company_customer_id = $companyCustomerWithDebt->id;

        $order = $this->orderService->checkout(
            $order->id,
            false,
            $this->getOrderData($order),
            $servicesData,
            $productsData,
            $paymentsData,
            $this->getOrderContactData(),
            $customerData
        );

        $this->tester->canSeeRecord(CompanyCustomer::class, [
            'id'      => $companyCustomerWithDebt->id,
            'balance' => 0
        ]);

        $this->tester->canSeeRecord(Order::class, ['status' => OrderConstants::STATUS_FINISHED]);

        $this->tester->canSeeRecord(Order::class, [
            'id'                 => $orderWithDebt1->id,
            'payment_difference' => 0
        ]);

        $this->tester->canSeeRecord(Order::class, [
            'id'                 => $orderWithDebt2->id,
            'payment_difference' => 0
        ]);

        verify($orderWithDebt1->getOrderPayments()->sum('amount'))->equals(5000);
        verify($orderWithDebt2->getOrderPayments()->sum('amount'))->equals(4000);

        $debtCostItem = CompanyCostItem::find()->select('{{%company_cost_items}}.id')->company($this->user->company_id)->isDebtPayment()->scalar();
        $this->tester->canSeeRecord(CompanyCashflow::class, [
            'cost_item_id' => $debtCostItem,
            'customer_id'  => $companyCustomerWithDebt->id,
            'value'        => 5000
        ]);
        $this->tester->canSeeRecord(CompanyCashflow::class, [
            'cost_item_id' => $debtCostItem,
            'customer_id'  => $companyCustomerWithDebt->id,
            'value'        => 4000
        ]);

        $paid = array_reduce($paymentsData, function ($sum, $orderPayment) {
            return $sum + $orderPayment->amount;
        });
        $actualPaymentsSum = array_reduce($order->orderPayments, function ($sum, $orderPayment) {
            return $sum + $orderPayment->amount;
        });

        verify($actualPaymentsSum)->equals($paid + $companyCustomerWithDebt->balance);
    }

    /**
     * @group debt
     */
    public function testAddDebt()
    {
        $companyCustomer = $this->tester->getFactory()->create(CompanyCustomer::class, [
            'balance'    => 0,
            'company_id' => $this->user->company_id
        ]);

        $price = $this->divisionService->price + $this->products[0]->price + $this->products[1]->price;
        $order = $this->tester->getFactory()->create(Order::class, [
            'payment_difference'  => -$price,
            'price'               => $price,
            'company_cash_id'     => $this->companyCash->id,
            'company_customer_id' => $companyCustomer->id,
            'created_user_id'     => $this->user->id,
            'division_id'         => $this->division->id,
            'staff_id'            => $this->staff->id,
            'status'              => OrderConstants::STATUS_ENABLED
        ]);

        $servicesData = $this->getOrderServiceData();
        $productsData = $this->getOrderProductData();

        $customerData = $this->getCustomerData();
        $customerData->company_customer_id = $companyCustomer->id;

        $order = $this->orderService->checkout(
            $order->id,
            false,
            $this->getOrderData($order),
            $servicesData,
            $productsData,
            [],
            $this->getOrderContactData(),
            $customerData
        );

        $this->tester->canSeeRecord(Order::class, ['status' => OrderConstants::STATUS_FINISHED]);

        $this->tester->canSeeRecord(CompanyCustomer::class, [
            'id'      => $companyCustomer->id,
            'balance' => -$order->price
        ]);
    }
}