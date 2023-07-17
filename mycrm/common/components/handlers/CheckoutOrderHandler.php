<?php

namespace common\components\handlers;

use common\components\events\order\CheckoutOrderEvent;
use core\forms\finance\CashflowForm;
use core\helpers\company\CashbackHelper;
use core\models\company\Cashback;
use core\models\order\Order;
use core\models\order\OrderPayment;
use core\models\order\OrderProduct;
use core\models\order\OrderService;
use core\services\CompanyCashflowService;
use core\services\customer\CompanyCustomerService;
use core\services\dto\PaymentData;
use core\services\dto\ProductData;
use core\services\dto\ServiceData;
use core\services\warehouse\dto\UsageDto;
use core\services\warehouse\dto\UsageProductDto;
use core\services\warehouse\UsageService;

class CheckoutOrderHandler
{
    /** @var CompanyCashflowService */
    private $cashflowService;
    /** @var CompanyCustomerService */
    private $customerService;
    /** @var UsageService */
    private $usageService;

    /**
     * CheckoutOrderHandler constructor.
     * @param CompanyCashflowService $cashflowService
     * @param CompanyCustomerService $customerService
     * @param UsageService $usageService
     */
    public function __construct(
        CompanyCashflowService $cashflowService,
        CompanyCustomerService $customerService,
        UsageService $usageService
    ) {
        $this->cashflowService = $cashflowService;
        $this->customerService = $customerService;
        $this->usageService = $usageService;
    }

    /**
     * @param CheckoutOrderEvent $event
     *
     * @throws \Throwable
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\StaleObjectException
     */
    public function handle(CheckoutOrderEvent $event)
    {
        $this->createCashflow($event);

        if ($event->order->depositExists() && $event->order->companyCustomer->hasDebt()) {
            $this->payDebt($event);
        }

        if ($event->order->depositExists()) {
            $this->depositMoney($event);
        }

        if ($event->order->getDepositPayment() > 0) {
            $this->payFromDeposit($event->order);
        }

        if ($event->order->getCashbackPayment() > 0) {
            $this->payFromCashback($event->order);
        }

        $this->writeOff($event);

        $this->chargeCashback($event->order);
    }

    /**
     * @param CheckoutOrderEvent $event
     *
     * @throws \Exception
     */
    private function createCashflow(CheckoutOrderEvent $event)
    {
        $cashflowData = new CashflowForm(\Yii::$app->user->id, [
            'date'        => $event->order->datetime,
            'cash_id'     => $event->order->company_cash_id,
            'comment'     => "Оплата за запись №{$event->order->number}",
            'customer_id' => $event->order->company_customer_id,
            'division_id' => $event->order->division_id,
            'staff_id'    => $event->order->staff_id,
            'value'       => $event->order->price
        ]);

        $services = $this->getServices($event->order->orderServices);
        $products = $this->getProducts($event->order->orderProducts);
        $payments = $this->getPayments($event->order->orderPayments);

        $cashflowData->value = $this->getValue($services, $payments);
        $paymentsForServices = $this->removeExcessFromPayments($cashflowData->value, $payments);
        $this->cashflowService->createFromServices($cashflowData, $event->order, $services, $paymentsForServices);

        if ($products) {
            $cashflowData->value = $this->getValue($products, $payments);
            $paymentsForProducts = $this->removeExcessFromPayments($cashflowData->value, $payments, true);
            $this->cashflowService->createFromProducts($cashflowData, $event->order, $products, $paymentsForProducts);
        }
    }

    /**
     * @param $orderServices
     * @return ServiceData[]
     */
    private function getServices($orderServices)
    {
        return array_map(function (OrderService $orderService) {
            return new ServiceData(
                $orderService->price,
                $orderService->division_service_id,
                $orderService->quantity,
                $orderService->discount
            );
        }, $orderServices);
    }

    /**
     * @param $orderProducts
     * @return ProductData[]
     */
    private function getProducts($orderProducts)
    {
        return array_map(function (OrderProduct $orderProduct) {
            return new ProductData(
                $orderProduct->product_id,
                $orderProduct->selling_price,
                $orderProduct->quantity
            );
        }, $orderProducts);
    }

    /**
     * @param $orderPayments
     * @return array
     */
    private function getPayments($orderPayments)
    {
        return array_map(function (OrderPayment $orderPayment) {
            return new PaymentData(
                $orderPayment->payment_id,
                $orderPayment->amount,
                $orderPayment->payment->isAccountable()
            );
        }, $orderPayments);
    }

    /**
     * @param $items
     * @param $payments
     * @return mixed
     */
    private function getValue($items, $payments)
    {
        $expectedPrice = array_reduce($items, function (int $sum, $item) {
            return $sum + $item->getSum();
        }, 0);

        $paid = array_reduce($payments, function (int $sum, PaymentData $paymentData) {
            if (!$paymentData->is_accountable) {
                return $sum;
            }
            return $sum + $paymentData->amount;
        }, 0);

        return min($expectedPrice, $paid);
    }

    /**
     * @param $value
     * @param PaymentData[] $payments
     * @param bool $ignoreNonAccountable
     * @return array
     */
    private function removeExcessFromPayments($value, $payments, $ignoreNonAccountable = false)
    {
        return array_filter(
            array_map(function (PaymentData $paymentData) use (&$value) {
                if ($value <= 0 && $paymentData->is_accountable) {
                    return new PaymentData($paymentData->payment_id, 0, $paymentData->is_accountable);
                }

                $payment = $paymentData->amount;
                if ($paymentData->is_accountable) {
                    $payment = min($value, $paymentData->amount);
                    $paymentData->amount -= $payment;
                    $value -= $payment;
                }

                return new PaymentData($paymentData->payment_id, $payment, $paymentData->is_accountable);
            }, $payments),
            function (PaymentData $paymentData) use ($ignoreNonAccountable) {
                $valid = $paymentData->amount > 0;
                if ($ignoreNonAccountable) {
                    $valid = $valid & $paymentData->is_accountable;
                }
                return $valid;
            }
        );
    }

    /**
     * @param CheckoutOrderEvent $event
     */
    private function payDebt(CheckoutOrderEvent $event)
    {
        $paymentForDebt = min(abs($event->order->companyCustomer->balance), $event->order->payment_difference);

        $event->order->editPaymentDifference($event->order->payment_difference - $paymentForDebt);

        $paymentsForDebt = $event->order->getExcessivePayments($paymentForDebt);

        $this->customerService->payDebt(
            $event->order->company_customer_id,
            $paymentsForDebt,
            \Yii::$app->user->id,
            new \DateTime($event->order->datetime)
        );

        $event->order->subtractDebtPayment($paymentsForDebt);

        foreach ($event->order->orderPayments as $orderPayment) {
            if ($orderPayment->amount == 0) {
                $orderPayment->delete();
            } else {
                $orderPayment->update();
            }
        }

        $event->order->update();
    }

    /**
     * @param $event
     *
     * @throws \yii\base\InvalidConfigException
     */
    private function depositMoney($event)
    {
        $this->cashflowService->addToDeposit(
            $event->order->company_cash_id,
            $event->order->division_id,
            $event->order->division->company_id,
            $event->order->company_customer_id,
            $event->order->payment_difference,
            \Yii::$app->user->id,
            $event->order
        );
    }

    /**
     * @param Order $order
     *
     * @throws \yii\base\InvalidConfigException
     */
    private function payFromDeposit(Order $order)
    {
        $this->cashflowService->withdrawFromDeposit(
            $order->company_cash_id,
            $order->division_id,
            $order->division->company_id,
            $order->company_customer_id,
            $order->getDepositPayment(),
            \Yii::$app->user->id,
            $order
        );
    }

    /**
     * @param Order $order
     */
    private function payFromCashback(Order $order)
    {
        $cashback = Cashback::add(
            CashbackHelper::TYPE_OUT,
            $order->getCashbackPayment(),
            $order->companyCustomer->cashback_percent,
            $order->companyCustomer->id
        );

        $cashback->save(false);

        $cashback->link('order', $order);
    }

    /**
     * @param CheckoutOrderEvent $event
     */
    private function writeOff(CheckoutOrderEvent $event)
    {
        if ($event->order->orderProducts) {
            $usageData = new UsageDto(
                $event->order->division->company_id,
                $event->order->division_id,
                $event->order->company_customer_id,
                $event->order->staff_id,
                0
            );

            $productsData = [];
            foreach ($event->order->orderProducts as $product) {
                $productData = new UsageProductDto($product->product_id, $product->quantity);
                $productData->setPurchasePrice($product->purchase_price);
                $productData->setSellingPrice($product->selling_price);
                $productsData[] = $productData;
            }

            $usage = $this->usageService->create($usageData, $productsData);

            $usage->link('order', $event->order);
        }
    }

    /**
     * @param Order $order
     */
    private function chargeCashback(Order $order)
    {
        $cashback_percent = $order->companyCustomer->cashback_percent > 0
            ? $order->companyCustomer->cashback_percent
            : $order->companyCustomer->company->cashback_percent;

        if ($cashback_percent > 0) {

            $amount = $this->getCashbackReward($order->orderPayments);

            if ($amount <= 0) {
                return;
            }

            $cashbackAmount = $order->companyCustomer->estimateCashback($amount, $cashback_percent);

            $cashback = Cashback::add(
                CashbackHelper::TYPE_IN,
                $cashbackAmount,
                $cashback_percent,
                $order->companyCustomer->id
            );

            $cashback->save(false);

            $cashback->link('order', $order);
        }
    }

    /**
     * @param $orderPayments
     * @return mixed
     */
    private function getCashbackReward($orderPayments)
    {
        return array_reduce(array_filter($orderPayments, function (OrderPayment $orderPayment) {
            return $orderPayment->payment->isCash() || $orderPayment->payment->isCard();
        }), function (int $sum, OrderPayment $orderPayment) {
            return $sum + $orderPayment->amount;
        }, 0);
    }
}
