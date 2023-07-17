<?php

namespace core\services\order;

use common\components\events\order\CheckoutOrderEvent;
use common\components\events\order\ResetOrderEvent;
use common\components\handlers\CheckoutOrderHandler;
use common\components\handlers\ResetOrderHandler;
use core\forms\order\OrderOverlapForm;
use core\helpers\company\PaymentHelper;
use core\helpers\order\OrderConstants;
use core\jobs\customer\RewardCustomerJob;
use core\models\company\Referrer;
use core\models\customer\CompanyCustomer;
use core\models\customer\CustomerSource;
use core\models\order\{
    OrderPayment, OrderProduct, OrderService
};
use core\models\order\Order;
use core\repositories\{
    CommentTemplateCategoryRepository, company\ReferrerRepository, CompanyCashflowRepository, CompanyCashRepository, customer\CompanyCustomerCategoryRepository, customer\CustomerSourceRepository, CustomerRepository, DivisionServiceRepository, exceptions\NotFoundException, PaymentRepository, StaffRepository
};
use core\repositories\customer\CompanyCustomerRepository;
use core\repositories\division\DivisionPaymentRepository;
use core\repositories\division\DivisionRepository;
use core\repositories\finance\CompanyCashflowProductRepository;
use core\repositories\finance\CompanyCashflowServiceRepository;
use core\repositories\order\{
    OrderPaymentRepository, OrderProductRepository, OrderRepository, OrderServiceRepository
};
use core\repositories\warehouse\ProductRepository;
use core\repositories\warehouse\UsageRepository;
use core\services\dto\CustomerData;
use core\services\order\dto\OrderContactData;
use core\services\order\dto\OrderData;
use core\services\order\dto\OrderPaymentData;
use core\services\order\dto\OrderServiceData;
use core\services\order\dto\ProductData;
use core\services\TransactionManager;
use Yii;
use yii\data\ActiveDataProvider;

class OrderModelService extends OrderServiceAbstract
{
    private $companyCashflowServiceRepository;
    private $companyCashflowProductRepository;
    private $commentTemplateCategoryRepository;
    private $customerSourceRepository;
    private $referrerRepository;

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
        TransactionManager $transactionManager,
        CommentTemplateCategoryRepository $commentTemplateCategoryRepository,
        CompanyCashflowServiceRepository $companyCashflowServiceRepository,
        CompanyCashflowProductRepository $companyCashflowProductRepository,
        DivisionPaymentRepository $divisionPaymentRepository,
        CustomerSourceRepository $customerSourceRepository,
        DivisionRepository $divisionRepository,
        ReferrerRepository $referrerRepository
    ) {
        $this->companyCashflowServiceRepository = $companyCashflowServiceRepository;
        $this->companyCashflowProductRepository = $companyCashflowProductRepository;
        $this->commentTemplateCategoryRepository = $commentTemplateCategoryRepository;
        $this->customerSourceRepository = $customerSourceRepository;
        $this->referrerRepository = $referrerRepository;
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
     * @param OrderData $orderData
     * @param OrderServiceData[] $servicesData
     * @param ProductData[] $productsData
     * @param OrderPaymentData[] $orderPaymentsData
     * @param OrderContactData[] $orderContactsData
     * @param CustomerData $customerData
     * @param string|null $customer_source_name
     * @param string|null $referrer_name
     *
     * @return Order
     * @throws \Exception
     */
    public function create(
        OrderData $orderData,
        $servicesData,
        $productsData,
        $orderPaymentsData,
        $orderContactsData,
        CustomerData $customerData,
        $customer_source_name = null,
        $referrer_name = null
    ) {
        $division = $this->divisionRepository->find(
            $orderData->division_id
        );
        $staff = $this->staffRepository->find($orderData->staff_id);
        $companyCash = $this->companyCashRepository->find(
            $orderData->company_cash_id
        );
        $orderContacts = $this->getOrderContacts(
            $orderContactsData,
            $orderData->company_id
        );
        $companyCustomer = $this->getCompanyCustomer(
            $customerData,
            $orderData->company_id
        );

        if (!empty($customer_source_name)) {
            $companyCustomer->setCustomerSource(CustomerSource::add(
                $customer_source_name,
                $orderData->company_id
            ));
        } elseif (!empty($customerData->source_id)) {
            $companyCustomer->setCustomerSource($this->customerSourceRepository->find($customerData->source_id));
        }

        $order = Order::add(
            $companyCustomer,
            $division,
            $staff,
            $companyCash,
            OrderConstants::TYPE_MANUAL,
            $orderData->created_user_id,
            $orderData->getDatetime(),
            $orderData->note,
            $orderData->notify_hours_before,
            $orderData->color,
            $orderData->insurance_company_id,
            $orderData->referrer_id
        );
        $order->setContacts($orderContacts);

        $orderServices = $this->getNewOrderServices($servicesData, $order);
        $orderProducts = $this->getNewOrderProducts($productsData, $order);

        $order->editDuration($this->getCalculatedDuration($orderServices));
        $order->editPrice(
            $this->getCalculatedPrice(
                $orderServices,
                $orderProducts
            )
        );
        $orderPayments = $this->getNewOrderPayments($orderPaymentsData, $order);

        $order->editPaymentDifference(
            $this->getPaymentDifference($orderPayments, $order->price)
        );

        if (!empty($referrer_name)) {
            $order->editReferrer(Referrer::add(
                $referrer_name,
                $orderData->company_id
            ));
        } elseif (!empty($orderData->referrer_id)) {
            $order->editReferrer($this->referrerRepository->find($orderData->referrer_id));
        }

        $order->setServices($orderServices);
        $order->setContacts($orderContacts);
        $order->setProducts($orderProducts);
        $order->setPayments($orderPayments);

        $this->transactionManager->execute(function () use ($order) {
            $this->orderRepository->save($order);
        });

        // Events
        $order->trigger(Order::EVENT_INSERT);

        return $order;
    }

    /**
     * @param integer $order_id
     * @param boolean $services_disabled
     * @param OrderData $orderData
     * @param OrderServiceData[] $servicesData
     * @param ProductData[] $productsData
     * @param OrderPaymentData[] $orderPaymentsData
     * @param OrderContactData[] $orderContactsData
     * @param CustomerData $customerData
     * @param string|null $customer_source_name
     * @param string|null $referrer_name
     *
     * @return Order
     * @throws \Exception
     */
    public function update(
        $order_id,
        $services_disabled,
        OrderData $orderData,
        $servicesData,
        $productsData,
        $orderPaymentsData,
        $orderContactsData,
        CustomerData $customerData,
        $customer_source_name = null,
        $referrer_name = null
    ) {
        $order = $this->orderRepository->find($order_id);
        $this->internalUpdate($order, $orderData, $servicesData, $productsData, $orderPaymentsData, $orderContactsData,
            $customerData, $services_disabled, $customer_source_name, $referrer_name);

        $this->transactionManager->execute(function () use ($order) {
            $this->orderRepository->save($order);

            $order->trigger(Order::EVENT_UPDATE);
        });

        return $order;
    }

    /**
     * @param integer $order_id
     * @param integer $duration
     *
     * @return Order
     * @throws \Exception
     */
    public function updateDuration($order_id, $duration)
    {
        $order = $this->orderRepository->find($order_id);
        $order->editDuration($duration);

        $orderServices = array_map(function (OrderService $orderService) use (&$duration) {
            $serviceDuration = $orderService->duration;
            $orderService->setDuration(min($duration, $serviceDuration));
            $duration = max(0, $duration - $serviceDuration);
            return $orderService;
        }, $order->orderServices);
        $orderDuration = $order->orderServices[0]->duration;
        $order->orderServices[0]->setDuration($orderDuration + $duration);

        $order->setServices($orderServices);

        $this->transactionManager->execute(function () use ($order, $orderServices) {
            $order->trigger(Order::EVENT_UPDATE);

            $this->orderRepository->save($order);
        });

        return $order;
    }

    /**
     * @param integer $order_id
     * @param integer $staff_id
     * @param string $datetime
     *
     * @return Order
     * @throws \Exception
     */
    public function move($order_id, $staff_id, $datetime)
    {
        $order = $this->orderRepository->find($order_id);
        $staff = $this->staffRepository->find($staff_id);
        $newDatetime = new \DateTime($datetime);
        $order->move($staff, $newDatetime);
        $this->transactionManager->execute(function () use ($order) {
            // Events
            $order->trigger(Order::EVENT_UPDATE);
            // Order
            $this->orderRepository->save($order);
        });
        return $order;
    }

    /**
     * @param                        $order_id
     * @param $services_disabled
     * @param OrderData $orderData
     * @param OrderServiceData[] $servicesData
     * @param ProductData[] $productsData
     * @param OrderPaymentData[] $orderPaymentsData
     * @param OrderContactData[] $orderContactsData
     * @param CustomerData $customerData
     *
     * @param bool $ignore_stock
     *
     * @return Order
     * @throws \yii\base\InvalidConfigException
     * @throws \Exception
     */
    public function checkout(
        $order_id,
        $services_disabled,
        OrderData $orderData,
        $servicesData,
        $productsData,
        $orderPaymentsData,
        $orderContactsData,
        CustomerData $customerData,
        $ignore_stock = false
    ) {
        $order = $this->orderRepository->find($order_id);
        $companyCustomer = $order->companyCustomer;

        $this->internalUpdate($order, $orderData, $servicesData, $productsData, $orderPaymentsData,
            $orderContactsData, $customerData, $services_disabled);

        $order->finish();
        if ($order->debtExists()) {
            $companyCustomer->addBalance($order->payment_difference);
        }

        $order->on(CheckoutOrderEvent::EVENT_NAME, [Yii::createObject(CheckoutOrderHandler::class), 'handle']);
        $order->on(CheckoutOrderEvent::EVENT_NAME, ['core\services\commands\NotificationService', 'addDelayedNotifications']);

        $this->transactionManager->execute(
            function () use ($order, $companyCustomer, $ignore_stock) {
                $this->orderRepository->save($order);
                $this->companyCustomerRepository->save($companyCustomer);

                $order->trigger(CheckoutOrderEvent::EVENT_NAME, new CheckoutOrderEvent(['order' => $order]));
                $order->trigger(Order::EVENT_CHECKOUT);
            }
        );

        \Yii::$app->queue->push(new RewardCustomerJob(['customerId' => $order->company_customer_id]));

        return $order;
    }

    /**
     * Set order status disabled
     *
     * @param $order_id
     *
     * @return Order
     * @throws \Exception
     */
    public function disable($order_id)
    {
        $order = $this->orderRepository->find($order_id);
        $order->disable();

        $this->transactionManager->execute(function () use ($order) {
            $this->orderRepository->save($order);
            $order->trigger(Order::EVENT_DISABLE);
        });

        return $order;
    }

    /**
     * Set order status enabled
     *
     * @param $order_id
     *
     * @return Order
     * @throws \Exception
     */
    public function enable($order_id)
    {
        $order = $this->orderRepository->find($order_id);
        $order->enable();

        $this->transactionManager->execute(function () use ($order) {
            $this->orderRepository->save($order);
            $order->trigger(Order::EVENT_ENABLE);
        });

        return $order;
    }

    /**
     * Set order status cancel
     *
     * @param $order_id
     *
     * @return Order
     * @throws \Exception
     */
    public function cancel($order_id)
    {
        $order = $this->orderRepository->find($order_id);
        $order->cancel();

        $this->transactionManager->execute(function () use ($order) {
            $this->orderRepository->save($order);
            $order->trigger(Order::EVENT_CANCEL);
        });

        return $order;
    }

    /**
     * Revert order to enable status
     *
     * @param $order_id
     *
     * @return Order
     * @throws \Exception
     */
    public function reset($order_id)
    {
        $order = $this->orderRepository->find($order_id);
        $companyCustomer = $this->companyCustomerRepository->find($order->company_customer_id);
        $needsRevert = $order->needsFinanceRevert();
        $order->reset();

        if ($order->debtExists()) {
            $companyCustomer->addBalance((-1) * $order->payment_difference);
        }

        $order->on(ResetOrderEvent::EVENT_NAME, [Yii::createObject(ResetOrderHandler::class), 'handle']);
        $order->on(ResetOrderEvent::EVENT_NAME,
            ['core\services\commands\NotificationService', 'removeDelayedNotifications']);

        $this->transactionManager->execute(function () use ($order, $companyCustomer, $needsRevert) {
            $this->orderRepository->save($order);

            $this->companyCustomerRepository->save($companyCustomer);

            $order->trigger(ResetOrderEvent::EVENT_NAME, new ResetOrderEvent(['order' => $order]));
            $order->trigger(Order::EVENT_RESET);
        });

        return $order;
    }

    /**
     * @param CustomerData $customerData
     * @param              $date
     * @param              $note
     * @param              $staff_id
     * @param              $division_id
     *
     * @return Order
     * @throws \Exception
     */
    public function addPending(CustomerData $customerData, $date, $note, $staff_id, $division_id)
    {
        $staff = $this->staffRepository->find($staff_id);
        $division = $this->divisionRepository->find($division_id);
        $companyCustomer = $this->getCompanyCustomer($customerData, $division->company_id);
        $companyCash = $this->companyCashRepository->findFirstByDivision($division->id);

        $order = Order::add(
            $companyCustomer,
            $division,
            $staff,
            $companyCash,
            1,
            \Yii::$app->user->id,
            $date,
            $note,
            0,
            $staff->color,
            null,
            null
        );
        $order->status = OrderConstants::STATUS_WAITING;

        $this->transactionManager->execute(function () use ($order) {
            $this->orderRepository->save($order);
            $order->trigger(Order::EVENT_WAITING);
        });

        return $order;
    }

    /**
     * @param              $id
     * @param CustomerData $customerData
     * @param              $date
     * @param              $note
     * @param              $staff_id
     * @param              $division_id
     *
     * @return Order
     * @throws \Exception
     */
    public function editPending($id, CustomerData $customerData, $date, $note, $staff_id, $division_id)
    {
        $order = $this->orderRepository->find($id);
        $staff = $this->staffRepository->find($staff_id);
        $division = $this->divisionRepository->find($division_id);
        $companyCustomer = $this->getCompanyCustomer($customerData, $division->company_id);
        $companyCash = $this->companyCashRepository->findFirstByDivision($division->id);

        $order->edit(
            $companyCustomer,
            $note,
            0,
            $companyCash->id,
            $staff->color,
            $date,
            null,
            null
        );
        $order->move($staff, new \DateTime($date));

        $this->transactionManager->execute(function () use ($order) {
            $this->orderRepository->save($order);
            // $order->trigger(Order::EVENT_UPDATE);
        });

        return $order;
    }

    /**
     * @param int $id
     */
    public function deletePending(int $id)
    {
        $order = $this->orderRepository->find($id);

        if (!$order->isPending()) {
            throw new \DomainException("Not allowed to delete.");
        }

        $this->orderRepository->delete($order);
    }

    /**
     * Returns new OrderService models
     * @param OrderServiceData[] $servicesData
     * @param Order $order
     * @return OrderService[]
     */
    protected function getNewOrderServices($servicesData, Order $order)
    {
        return array_map(function (OrderServiceData $data) use ($order) {
            $divisionService = $this->divisionServiceRepository->find($data->division_service_id);
            return OrderService::add(
                $order,
                $divisionService,
                $data->discount,
                $data->duration,
                $data->price,
                $data->quantity
            );
        }, $servicesData);
    }

    /**
     * Returns new CompanyCustomer models
     *
     * @param OrderContactData[] $contactsData
     * @param integer $company_id
     *
     * @return CompanyCustomer[]
     */
    protected function getOrderContacts($contactsData, $company_id)
    {
        return array_map(function (OrderContactData $data) use ($company_id) {
            return $this->getCompanyCustomer(
                new CustomerData(
                    $data->id,
                    $data->name,
                    null,
                    null,
                    $data->phone,
                    null
                ),
                $company_id
            );
        }, $contactsData);
    }

    /**
     * Returns new OrderPayment models
     * @param OrderPaymentData[] $orderPaymentsData
     * @param Order $order
     * @return OrderPayment[]
     */
    protected function getNewOrderPayments($orderPaymentsData, Order $order)
    {
        $totalPayment = 0;
        $hasNotAccountablePayment = false;

        $payments = [];
        if (!empty($orderPaymentsData)) {
            $payments = array_map(function (OrderPaymentData $paymentData) use (
                $order,
                &$totalPayment,
                &$hasNotAccountablePayment
            ) {
                $payment = $this->paymentRepository->find($paymentData->payment_id);

                if ($payment->type == PaymentHelper::CASHBACK && $order->companyCustomer->cashback_balance < $paymentData->amount) {
                    throw new \DomainException(Yii::t('app', "Customer has less cashback than required."));
                }

                if (!$payment->isAccountable()) {
                    $hasNotAccountablePayment = true;
                    if ($paymentData->amount > $order->price) {
                        throw new \DomainException("Сумма оплаты для \"{$payment->getLabel()}\" не может превышать цену за запись.");
                    }
                }

                $totalPayment += $paymentData->amount;

                return OrderPayment::add(
                    $order,
                    $payment,
                    $payment->isDeposit() ? 0 : $paymentData->amount
                );
            }, $orderPaymentsData);
        }

        if ($totalPayment > $order->price && $hasNotAccountablePayment) {
            throw new \DomainException("Сумма оплат превышает цену записи. Переплата не может быть соверешена с не учитываевыми способами оплат "
                . "(Кэшбек, страховка и сертифика)");
        }

        $payments = $this->addDepositPayment($order, $payments);

        return $payments;
    }

    /**
     * Returns new OrderServiceProduct models
     * @param ProductData[] $productsData
     * @param Order $order
     * @return OrderProduct[]
     */
    protected function getNewOrderProducts($productsData, $order)
    {
        $orderProducts = [];

        foreach ($productsData as $productData) {
            /* @var ProductData $productData */
            $product = $this->productRepository->find($productData->product_id);
            $orderProducts[] = OrderProduct::add(
                $order,
                $product,
                $productData->quantity,
                $product->purchase_price,
                $productData->selling_price
            );
        }

        return $orderProducts;
    }

    /**
     * Returns OrderService models found by id or returns new
     * @param OrderServiceData[] $servicesData
     * @param Order $order
     * @return OrderService[]
     */
    protected function getOrderServices($servicesData, Order $order)
    {
        return array_map(function (OrderServiceData $data) use ($order) {
            try {
                $orderService = $this->orderServiceRepository->find($data->id);
                $orderService->edit($data->discount, $data->duration, $data->price, $data->quantity);
                $orderService->revertDeletion();
            } catch (NotFoundException $e) {
                $divisionService = $this->divisionServiceRepository->find($data->division_service_id);
                $orderService = OrderService::add(
                    $order,
                    $divisionService,
                    $data->discount,
                    $data->duration,
                    $data->price,
                    $data->quantity
                );
            }

            return $orderService;
        }, $servicesData);
    }

    /**
     * Returns OrderPayment models found by id or returns new
     * @param OrderPaymentData[] $orderPaymentsData
     * @param Order $order
     * @return OrderPayment[]
     */
    protected function getOrderPayments($orderPaymentsData, Order $order)
    {
        $orderPayments = $this->orderPaymentRepository->findByOrder($order->id);

        $totalPayment = 0;
        $hasNotAccountablePayment = false;

        $orderPaymentModels = array_map(function (OrderPaymentData $paymentData) use (
            $order,
            &$orderPayments,
            &$totalPayment,
            &$hasNotAccountablePayment
        ) {
            if (isset($orderPayments[$paymentData->payment_id])) {
                $orderPayment = $orderPayments[$paymentData->payment_id];
                $orderPayment->edit($paymentData->amount);
                unset($orderPayments[$paymentData->payment_id]);
            } else {
                $payment = $this->paymentRepository->find($paymentData->payment_id);
                $orderPayment = OrderPayment::add(
                    $order,
                    $payment,
                    $paymentData->amount
                );
            }

            if ($orderPayment->payment->isDeposit()) {
                $orderPayment->amount = 0;
            }

            if ($orderPayment->payment->isCashBack() && $order->companyCustomer->cashback_balance < $paymentData->amount) {
                throw new \DomainException(Yii::t('app', "Customer has less cashback than required."));
            }

            if (!$orderPayment->payment->isAccountable()) {
                $hasNotAccountablePayment = true;
                if ($orderPayment->amount > $order->price) {
                    throw new \DomainException("Оплата для \"{$orderPayment->payment->getLabel()}\" не может превышать цену за запись.");
                }
            }

            $totalPayment += $paymentData->amount;

            return $orderPayment;
        }, $orderPaymentsData);

        if ($totalPayment > $order->price && $hasNotAccountablePayment) {
            throw new \DomainException("Сумма оплаты превышает запись. Переплата не может быть совершена с не учитываевыми способами оплат "
                . "(Кэшбек, страховка и сертификат)");
        }

        foreach ($orderPayments as $orderPayment) {
            $orderPayment->edit(0);
            array_push($orderPaymentModels, $orderPayment);
        }

        $orderPaymentModels = $this->addDepositPayment($order, $orderPaymentModels);

        return $orderPaymentModels;
    }

    /**
     * Returns OrderServiceProduct models found by id or returns new
     * @param ProductData[] $productsData
     * @param Order $order
     * @return OrderProduct[]
     */
    protected function getOrderProducts($productsData, Order $order)
    {
        $orderProducts = [];

        foreach ($productsData as $productData) {
            /* @var ProductData $productData */
            $product = $this->productRepository->find($productData->product_id);

            try {
                $orderProduct = $this->orderProductRepository->findByOrderAndProduct(
                    $order->id,
                    $product->id
                );
                $orderProduct->edit(
                    $productData->quantity,
                    $product->purchase_price,
                    $productData->selling_price
                );
                $orderProduct->revertDeletion();
            } catch (NotFoundException $e) {
                $orderProduct = OrderProduct::add(
                    $order,
                    $product,
                    $productData->quantity,
                    $product->purchase_price,
                    $productData->selling_price
                );
            }

            $orderProducts[] = $orderProduct;
        }
        return $orderProducts;
    }

    /**
     * @param OrderOverlapForm $form
     * @return bool
     */
    public function isOrderOverlapping(OrderOverlapForm $form)
    {
        return $this->orderRepository->findExistingOrderOfStaff($form->start, $form->end, $form->division_id,
            $form->staff_id);
    }

    /**
     * @param OrderService[] $orderServices
     * @return bool
     */
    protected function hasNewServices($orderServices)
    {
        foreach ($orderServices as $orderService) {
            if ($orderService->isNewRecord) {
                return true;
            }
        }
        return false;
    }

    /**
     * Export in excel format
     * @param ActiveDataProvider $dataProvider
     */
    public function export(ActiveDataProvider $dataProvider)
    {
        $dataProvider->pagination->pageSize = 0;
        $dataProvider->query->orderBy(['datetime' => SORT_DESC]);

//        $orders = $dataProvider->models;

        ob_start();

        $ea = new \PHPExcel(); // ea is short for Excel Application
        $ea->getProperties()
            ->setCreator('MyCrm.kz')
            ->setTitle('PHPExcel')
            ->setLastModifiedBy('MyCrm.kz')
            ->setDescription('')
            ->setSubject('')
            ->setKeywords('excel php')
            ->setCategory('');
        $ews = $ea->getSheet(0);
        $ews->setTitle('Записи');

        $order = new Order();
        $data = [
            [
                $order->getAttributeLabel('id'),
                $order->getAttributeLabel('number'),
                $order->getAttributeLabel('staff_id'),
                Yii::t('app', 'Services'),
                Yii::t('app', 'Categories'),
                $order->getAttributeLabel('company_customer_id'),
                $order->getAttributeLabel('customer_phone'),
                Yii::t('app', 'Datetime'),
                $order->getAttributeLabel('status'),
                $order->getAttributeLabel('created_user_id'),
                $order->getAttributeLabel('comments'),
                $order->getAttributeLabel('type'),
                $order->getAttributeLabel('price'),
                Yii::t('app', 'Paid, currency'),
                Yii::t('app', 'Customer Source'),
            ]
        ];

        $rows = 1;
        foreach ($dataProvider->query->each(100) as $iter) {
            /* @var $iter Order */
            $created_info = "";
            switch ($iter->type) {
                case OrderConstants::TYPE_MANUAL:
                    $created_info .= isset($iter->createdUser) ? $iter->createdUser->username . "\r" : "";
                    break;
                case OrderConstants::TYPE_APPLICATION:
                    $created_info .= Yii::t('app', 'application') . "\r";
                    break;
            }

            $orderTotalPayment = $iter->getPaidTotal();
            $companyCustomer = $iter->companyCustomer;

            foreach ($iter->orderServices as $key => $orderService) {
                $rows++;
                $servicePayment = min($orderTotalPayment, $orderService->price);
                $orderTotalPayment -= $servicePayment;
                $source_name = $companyCustomer->source_id ? $companyCustomer->source->name : null;
                $data[] = [
                    $iter->id,
                    $iter->number,
                    $iter->staff->getFullName(),
                    $orderService->divisionService->service_name,
                    $orderService->divisionService->getCategoriesTitle(", "),
                    $iter->companyCustomer->customer->name,
                    $iter->companyCustomer->customer->phone,
                    Yii::$app->formatter->asDate($iter->datetime) . "\r"
                    . Yii::$app->formatter->asTime($iter->datetime),
                    OrderConstants::getStatuses()[$iter->status],
                    $created_info,
                    $iter->note,
                    OrderConstants::getTypes()[$iter->type],
                    $orderService->getFinalPrice(),
                    $servicePayment,
                    $source_name
                ];
            }
        }

        $ews->fromArray($data, ' ', 'A1', true);
        $ews->setCellValue('N'.($rows+1), "=SUM(N2:N{$rows})");
        $ews->getColumnDimension('A')->setWidth(4); // ID
        $ews->getColumnDimension('B')->setWidth(12); // Key
        $ews->getColumnDimension('C')->setWidth(26); // Fullname
        $ews->getColumnDimension('D')->setWidth(26); // Service name
        $ews->getColumnDimension('E')->setWidth(18); // Client fullname
        $ews->getColumnDimension('F')->setWidth(18); // Client phone
        $ews->getColumnDimension('G')->setWidth(18); // Datetime
        $ews->getColumnDimension('H')->setWidth(18); // Creator
        $ews->getColumnDimension('I')->setWidth(20); // Type
        $ews->getColumnDimension('J')->setWidth(14);  // Note
        $ews->getColumnDimension('K')->setWidth(20);  // Source

        $size = sizeof($data);
        $ea->getActiveSheet()->getStyle("E1:E{$size}")->getAlignment()
            ->setWrapText(true);
        $ea->getActiveSheet()->getStyle("G1:G{$size}")->getAlignment()
            ->setWrapText(true);

        header('Content-Type: application/vnd.ms-excel');
        $filename = "Записи_" . date("d-m-Y-His") . ".xls";
        header('Content-Disposition: attachment;filename=' . $filename . ' ');
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($ea, 'Excel5');
        $objWriter->save('php://output');

        ob_end_flush();
    }

    /**
     * @param Order $order
     * @param array $orderPaymentModels
     * @return array
     */
    private function addDepositPayment(Order $order, array $orderPaymentModels)
    {
        $paid = array_reduce($orderPaymentModels, function (int $sum, $payment) {
            return $sum + $payment->amount;
        }, 0);

        if ($paid < $order->price && $order->companyCustomer->balance > 0) {
            $remainder = $order->price - $paid;

            $orderPayment = current(array_filter($orderPaymentModels, function (OrderPayment $orderPayment) {
                return $orderPayment->payment->isDeposit();
            }));

            $amount = min($order->companyCustomer->balance, $remainder);

            if (!$orderPayment) {
                $payment = \core\models\Payment::findOne(['type' => PaymentHelper::DEPOSIT]);
                $orderPayment = OrderPayment::add($order, $payment, $amount);
                $orderPaymentModels[] = $orderPayment;
            }

            $orderPayment->amount = $amount;
        } else {
            foreach ($orderPaymentModels as $orderPaymentModel) {
                if ($orderPaymentModel->payment->isDeposit()) {
                    $orderPaymentModel->amount = 0;
                }
            }
        }

        return $orderPaymentModels;
    }

    /**
     * @param Order $order
     * @param OrderData $orderData
     * @param $servicesData
     * @param $productsData
     * @param $orderPaymentsData
     * @param $orderContactsData
     * @param CustomerData $customerData
     * @param boolean $services_disabled
     * @param null $customer_source_name
     * @param null $referrer_name
     * @return Order
     * @throws \Exception
     */
    private function internalUpdate(
        Order $order,
        OrderData $orderData,
        $servicesData,
        $productsData,
        $orderPaymentsData,
        $orderContactsData,
        CustomerData $customerData,
        $services_disabled,
        $customer_source_name = null,
        $referrer_name = null
    ) {
        $staff = $this->staffRepository->find($orderData->staff_id);
        $companyCustomer = $this->getCompanyCustomer($customerData, $orderData->company_id);

        if (!empty($customer_source_name)) {
            $companyCustomer->setCustomerSource(CustomerSource::add(
                $customer_source_name,
                $orderData->company_id
            ));
        } elseif (!empty($customerData->source_id)) {
            $companyCustomer->setCustomerSource($this->customerSourceRepository->find($customerData->source_id));
        }

        $order->edit(
            $companyCustomer,
            $orderData->note,
            $orderData->notify_hours_before,
            $orderData->company_cash_id,
            $orderData->color,
            $orderData->getDatetime(),
            $orderData->insurance_company_id,
            $orderData->referrer_id
        );
        $order->move($staff, $orderData->datetime);

        if (!$order->services_disabled || $order->canAdminOrder()) {
            $orderServices = $this->getOrderServices($servicesData, $order);
            $order->setServices($orderServices);
            $orderProducts = $this->getOrderProducts($productsData, $order);
            $order->setProducts($orderProducts);
            $order->editPrice($this->getCalculatedPrice($orderServices, $orderProducts));
            $order->editDuration($this->getCalculatedDuration($orderServices));
        }

        if (!empty($referrer_name)) {
            $order->editReferrer(Referrer::add(
                $referrer_name,
                $orderData->company_id
            ));
        } elseif (!empty($orderData->referrer_id)) {
            $order->editReferrer($this->referrerRepository->find($orderData->referrer_id));
        }

        // Update Contacts
        $orderContacts = $this->getOrderContacts($orderContactsData, $orderData->company_id);
        $order->setContacts($orderContacts);

        // Order payments
        $orderPayments = $this->getOrderPayments($orderPaymentsData, $order);
        $order->editPaymentDifference(
            $this->getPaymentDifference($orderPayments, $order->price)
        );
        $order->setPayments($orderPayments);
        $order->disableServices($services_disabled);

        return $order;
    }
}
