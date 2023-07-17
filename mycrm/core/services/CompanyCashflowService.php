<?php

namespace core\services;

use core\forms\finance\CashflowForm;
use core\forms\finance\CashflowUpdateForm;
use core\models\customer\CompanyCustomer;
use core\models\finance\CompanyCashflow;
use core\models\finance\CompanyCashflowPayment;
use core\models\finance\CompanyCashflowProduct;
use core\models\finance\CompanyCashflowService as CashflowService;
use core\models\order\Order;
use core\models\Payment;
use core\repositories\{
    company\CompanyRepository, CompanyCashflowRepository, CompanyCostItemRepository, customer\CompanyCustomerRepository, division\DivisionRepository, StaffRepository, user\UserRepository
};
use core\repositories\finance\CompanyCashflowProductRepository;
use core\repositories\finance\CompanyCashflowServiceRepository;
use core\repositories\order\{
    OrderPaymentRepository, OrderProductRepository, OrderRepository, OrderServiceRepository
};
use core\services\dto\PaymentData;
use core\services\dto\ProductData;
use core\services\dto\ServiceData;

class CompanyCashflowService
{
    private $companyCashflowRepository;
    private $orderRepository;
    private $transactionManager;
    private $divisionRepository;
    private $companyCostItemRepository;
    private $userRepository;
    private $staffRepository;
    private $orderServiceRepository;
    private $orderServiceProductRepository;
    private $companyCashflowServiceRepository;
    private $companyCashflowProductRepository;
    private $companyRepository;
    private $companyCustomerRepository;
    private $orderPaymentRepository;

    /**
     * CompanyCashflowService constructor.
     * @param CompanyCashflowRepository $companyCashflowRepository
     * @param OrderRepository $orderRepository
     * @param StaffRepository $staffRepository
     * @param UserRepository $userRepository
     * @param OrderServiceRepository $orderServiceRepository
     * @param DivisionRepository $divisionRepository
     * @param CompanyCostItemRepository $companyCostItemRepository
     * @param OrderProductRepository $orderServiceProductRepository
     * @param CompanyCashflowServiceRepository $companyCashflowServiceRepository
     * @param CompanyCashflowProductRepository $companyCashflowProductRepository
     * @param CompanyRepository $companyRepository
     * @param CompanyCustomerRepository $companyCustomerRepository
     * @param OrderPaymentRepository $orderPaymentRepository
     * @param TransactionManager $transactionManager
     */
    public function __construct(
        CompanyCashflowRepository $companyCashflowRepository,
        OrderRepository $orderRepository,
        StaffRepository $staffRepository,
        UserRepository $userRepository,
        OrderServiceRepository $orderServiceRepository,
        DivisionRepository $divisionRepository,
        CompanyCostItemRepository $companyCostItemRepository,
        OrderProductRepository $orderServiceProductRepository,
        CompanyCashflowServiceRepository $companyCashflowServiceRepository,
        CompanyCashflowProductRepository $companyCashflowProductRepository,
        CompanyRepository $companyRepository,
        CompanyCustomerRepository $companyCustomerRepository,
        OrderPaymentRepository $orderPaymentRepository,
        TransactionManager $transactionManager
    ) {
        $this->companyCashflowServiceRepository = $companyCashflowServiceRepository;
        $this->companyCashflowProductRepository = $companyCashflowProductRepository;
        $this->orderServiceProductRepository = $orderServiceProductRepository;
        $this->orderServiceRepository = $orderServiceRepository;
        $this->userRepository = $userRepository;
        $this->companyCostItemRepository = $companyCostItemRepository;
        $this->staffRepository = $staffRepository;
        $this->companyRepository = $companyRepository;
        $this->divisionRepository = $divisionRepository;
        $this->companyCashflowRepository = $companyCashflowRepository;
        $this->orderRepository = $orderRepository;
        $this->transactionManager = $transactionManager;
        $this->companyCustomerRepository = $companyCustomerRepository;
        $this->orderPaymentRepository = $orderPaymentRepository;
    }

    /**
     * @param CashflowForm $form
     *
     * @return CompanyCashflow
     * @throws \Exception
     */
    public function add(CashflowForm $form)
    {
        $companyCashflow = CompanyCashFlow::add(
            $form->date,
            $form->cash_id,
            $form->comment,
            $form->getCompanyId(),
            $form->contractor_id,
            $form->cost_item_id,
            $form->customer_id,
            $form->division_id,
            $form->receiver_mode,
            $form->staff_id,
            $form->value,
            $form->getUserId()
        );

        $payments = $this->getPayments($companyCashflow, $form->payments);

        $this->transactionManager->execute(function () use ($companyCashflow, $payments) {
            $this->companyCashflowRepository->add($companyCashflow);
            foreach ($payments as $payment) {
                $this->companyCashflowRepository->add($payment);
            }
        });

        return $companyCashflow;
    }

    /**
     * @param $id
     * @param CashflowUpdateForm $form
     * @return CompanyCashflow
     * @throws \Exception
     */
    public function edit(
        $id,
        CashflowUpdateForm $form
    ) {
        $companyCashflow = $this->companyCashflowRepository->find($id);
        $companyCashflow->edit(
            $form->date,
            $form->cash_id,
            $form->comment,
            $form->getCompanyId(),
            $form->contractor_id,
            $form->cost_item_id,
            $form->customer_id,
            $form->division_id,
            $form->receiver_mode,
            $form->staff_id,
            $form->value,
            $form->getUserId()
        );

        $payments = $this->getPayments($companyCashflow, $form->payments);

        $this->transactionManager->execute(function () use ($companyCashflow, $payments) {
            $this->companyCashflowRepository->edit($companyCashflow);

            CompanyCashflowPayment::deleteAll(['cashflow_id' => $companyCashflow->id]);
            foreach ($payments as $payment) {
                $this->companyCashflowRepository->add($payment);
            }
        });

        return $companyCashflow;
    }

    /**
     * @param int $id
     * @return CompanyCashflow
     */
    public function delete(int $id)
    {
        $model = $this->companyCashflowRepository->find($id);
        $model->softDelete();
        return $model;
    }

    /**
     * @param CashflowForm  $form
     * @param Order         $order
     * @param PaymentData[] $payments
     *
     * @return CompanyCashflow
     * @throws \Exception
     */
    public function refund(CashflowForm $form, Order $order, $payments)
    {
        $costItem = $this->companyCostItemRepository->findRefundCostItem($form->getCompanyId());

        $cashflow = CompanyCashflow::add(
            $form->date,
            $form->cash_id,
            $form->comment,
            $form->getCompanyId(),
            null,
            $costItem->id,
            $form->customer_id,
            $form->division_id,
            CompanyCashflow::RECEIVER_STAFF,
            $form->staff_id,
            $form->value,
            $form->getUserId()
        );
        $cashflow->setOrderRelation($order);

        $cashflow->payments = array_map(function (PaymentData $paymentData) {
            return new CompanyCashflowPayment([
                'payment_id' => $paymentData->payment_id,
                'value'      => $paymentData->amount
            ]);
        }, $payments);

        $this->transactionManager->execute(function () use ($cashflow) {
            $this->companyCashflowRepository->save($cashflow);
        });

        return $cashflow;
    }

    /**
     * @param int   $company_cash_id
     * @param int   $division_id
     * @param int   $company_id
     * @param int   $company_customer_id
     * @param int   $amount
     * @param int   $user_id
     * @param Order $order
     *
     * @throws \yii\base\InvalidConfigException
     */
    public function withdrawFromDeposit(
        int $company_cash_id,
        int $division_id,
        int $company_id,
        int $company_customer_id,
        int $amount,
        int $user_id,
        Order $order
    ) {
        $companyCustomer = CompanyCustomer::findOne($company_customer_id);
        $depositExpense = $this->companyCostItemRepository->findDepositExpense($company_id);

        $companyCustomer->addBalance((-1) * $amount);
        $comment = "Снятие средств с депозита: {$companyCustomer->customer->getFullName()}. Депозит: " . \Yii::$app->formatter->asDecimal($companyCustomer->balance);

        $cashflow = CompanyCashflow::add(
            date("Y-m-d H:i:s"),
            $company_cash_id,
            $comment,
            $company_id,
            null,
            $depositExpense->id,

            $company_customer_id,
            $division_id,
            CompanyCashflow::RECEIVER_CUSTOMER,
            $order->staff_id,
            $amount,
            $user_id
        );
        $cashflow->setOrderRelation($order);
        $payment = CompanyCashflowPayment::add($cashflow, Payment::CASH_ID, $amount);

        $this->companyCashflowRepository->edit($companyCustomer);
        $this->companyCashflowRepository->add($cashflow);
        $this->companyCashflowRepository->add($payment);
    }

    /**
     * @param int   $company_cash_id
     * @param int   $division_id
     * @param int   $company_id
     * @param int   $company_customer_id
     * @param int   $amount
     * @param int   $user_id
     * @param Order $order
     *
     * @throws \yii\base\InvalidConfigException
     */
    public function addToDeposit(
        int $company_cash_id,
        int $division_id,
        int $company_id,
        int $company_customer_id,
        int $amount,
        int $user_id,
        Order $order
    ) {
        $companyCustomer = CompanyCustomer::findOne($company_customer_id);

        $depositIncome = $this->companyCostItemRepository->findDepositIncome($company_id);

        $companyCustomer->addBalance($amount);

        $comment = "Начисление средств на депозит: {$companyCustomer->customer->getFullName()}. Депозит: " . \Yii::$app->formatter->asDecimal($companyCustomer->balance);

        $cashflow = CompanyCashflow::add(
            date("Y-m-d H:i:s"),
            $company_cash_id,
            $comment,
            $company_id,
            null,
            $depositIncome->id,
            $company_customer_id,
            $division_id,
            CompanyCashflow::RECEIVER_CUSTOMER,
            $order->staff_id,
            $amount,
            $user_id
        );
        $cashflow->setOrderRelation($order);
        $payment = CompanyCashflowPayment::add($cashflow, Payment::CASH_ID, $amount);

        $this->companyCashflowRepository->edit($companyCustomer);
        $this->companyCashflowRepository->add($cashflow);
        $this->companyCashflowRepository->add($payment);
    }

    /**
     * @param CompanyCashflow $cashflow
     * @param array $payments
     * @return CompanyCashflowPayment[]
     */
    private function getPayments(CompanyCashflow $cashflow, array $payments)
    {
        return array_map(function (array $paymentData) use ($cashflow) {
            $divisionPayment = $this->divisionRepository->findPayment($cashflow->division_id,
                $paymentData['payment_id']);

            return CompanyCashflowPayment::add($cashflow, $divisionPayment->payment_id, $paymentData['value']);
        }, $payments);
    }

    /**
     * @param CashflowForm  $form
     * @param Order         $order
     * @param ServiceData[] $services
     * @param PaymentData[] $payments
     *
     * @return CompanyCashflow
     * @throws \Exception
     */
    public function createFromServices(CashflowForm $form, Order $order, array $services, array $payments)
    {
        $costItem = $this->companyCostItemRepository->findOrderCostItemByCompany($form->getCompanyId());

        $companyCashflow = CompanyCashflow::add(
            $form->date,
            $form->cash_id,
            $form->comment,
            $form->getCompanyId(),
            null,
            $costItem->id,
            $form->customer_id,
            $form->division_id,
            CompanyCashflow::RECEIVER_STAFF,
            $form->staff_id,
            $form->value,
            $form->getUserId()
        );
        $companyCashflow->setOrderRelation($order);

        $companyCashflow->services = array_map(function (ServiceData $serviceData) {
            return new CashflowService([
                'service_id' => $serviceData->service_id,
                'discount'   => $serviceData->discount,
                'price'      => $serviceData->price,
                'quantity'   => $serviceData->quantity
            ]);
        }, $services);

        $companyCashflow->payments = array_map(function (PaymentData $paymentData) {
            return new CompanyCashflowPayment([
                'payment_id' => $paymentData->payment_id,
                'value'      => $paymentData->amount
            ]);
        }, $payments);

        $this->transactionManager->execute(function () use ($companyCashflow) {
            $this->companyCashflowRepository->save($companyCashflow);
        });

        return $companyCashflow;
    }

    /**
     * @param CashflowForm $form
     * @param Order        $order
     * @param array        $products
     * @param array        $payments
     *
     * @return CompanyCashflow
     * @throws \Exception
     */
    public function createFromProducts(CashflowForm $form, Order $order, array $products, array $payments)
    {
        $costItem = $this->companyCostItemRepository->findOrderProductCostItemByCompany($form->getCompanyId());

        $companyCashflow = CompanyCashFlow::add(
            $form->date,
            $form->cash_id,
            $form->comment,
            $form->getCompanyId(),
            null,
            $costItem->id,
            $form->customer_id,
            $form->division_id,
            CompanyCashflow::RECEIVER_STAFF,
            $form->staff_id,
            $form->value,
            $form->getUserId()
        );
        $companyCashflow->setOrderRelation($order);

        $companyCashflow->products = array_map(function (ProductData $productData) {
            return new CompanyCashflowProduct([
                'product_id' => $productData->product_id,
                'discount'   => $productData->discount,
                'price'      => $productData->price,
                'quantity'   => $productData->quantity
            ]);
        }, $products);

        $companyCashflow->payments = array_map(function (PaymentData $paymentData) {
            return new CompanyCashflowPayment([
                'payment_id' => $paymentData->payment_id,
                'value'      => $paymentData->amount
            ]);
        }, $payments);

        $this->transactionManager->execute(function () use ($companyCashflow) {
            $this->companyCashflowRepository->save($companyCashflow);
        });

        return $companyCashflow;
    }

    /**
     * @param int $id
     * @throws \Exception
     */
    public function deleteDebtPayment(int $id)
    {
        $model = $this->companyCashflowRepository->find($id);

        if (!$model->isDeletableDebtPayment()) {
            throw new \DomainException("Погашение долга не может быть удалено.");
        }

        $companyCustomer = $model->customer;
        $order = $model->order;
        $orderPayments = $order->orderPayments;
        $amount = $model->value;

        $order->editPaymentDifference($order->payment_difference - $amount);

        foreach ($orderPayments as $orderPayment) {
            if ($orderPayment->payment->isAccountable() && $amount > 0) {
                $difference = min($orderPayment->amount, $amount);
                $orderPayment->amount -= $difference;
                $amount -= $difference;
            }
        }

        $companyCustomer->addBalance((-1) * $model->value);

        $model->softDelete();

        $this->transactionManager->execute(function () use ($model, $companyCustomer, $order, $orderPayments) {
            $this->companyCashflowRepository->edit($model);
            $this->companyCustomerRepository->save($companyCustomer);
            $this->orderRepository->save($order);
            foreach ($orderPayments as $orderPayment) {
                $this->orderPaymentRepository->save($orderPayment);
            }
        });
    }
}
