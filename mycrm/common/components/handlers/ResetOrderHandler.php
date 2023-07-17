<?php

namespace common\components\handlers;

use common\components\events\order\ResetOrderEvent;
use core\forms\finance\CashflowForm;
use core\helpers\company\CashbackHelper;
use core\models\order\Order;
use core\models\order\OrderPayment;
use core\services\CompanyCashflowService;
use core\services\dto\PaymentData;
use core\services\warehouse\UsageService;

class ResetOrderHandler
{
    /** @var CompanyCashflowService */
    private $cashflowService;
    /** @var UsageService */
    private $usageService;

    /**
     * ResetOrderHandler constructor.
     * @param CompanyCashflowService $cashflowService
     * @param UsageService $usageService
     */
    public function __construct(
        CompanyCashflowService $cashflowService,
        UsageService $usageService
    ) {
        $this->cashflowService = $cashflowService;
        $this->usageService = $usageService;
    }

    public function handle(ResetOrderEvent $event)
    {
        $this->revertPaymentFromCashback($event->order);

        $this->revertPaymentFromDeposit($event->order);

        $this->revertCashback($event->order);

        $this->revertDeposit($event->order);

        $this->revertCashflow($event->order);

        $this->revertWriteOff($event->order);
    }

    /**
     * @param Order $order
     */
    private function revertPaymentFromCashback(Order $order)
    {
        if ($order->getCashbackPayment() > 0) {
            $cashback = $order->getCashbacks()->enabled()->andWhere([
                'type' => CashbackHelper::TYPE_OUT
            ])->one();

            if ($cashback) {
                $cashback->softDelete();
                $cashback->save(false);

                $order->companyCustomer->addCashback($cashback->amount);
                $order->companyCustomer->save(false);
            }
        }
    }

    /**
     * @param Order $order
     *
     * @throws \yii\base\InvalidConfigException
     */
    private function revertPaymentFromDeposit(Order $order)
    {
        if ($order->getDepositPayment()) {
            $this->cashflowService->addToDeposit(
                $order->company_cash_id,
                $order->division->id,
                $order->division->company_id,
                $order->company_customer_id,
                $order->getDepositPayment(),
                \Yii::$app->user->id,
                $order
            );
        }
    }

    /**
     * @param Order $order
     */
    private function revertCashback(Order $order)
    {
        $cashback = $order->getCashbacks()->enabled()->andWhere([
            'type' => CashbackHelper::TYPE_IN
        ])->one();

        if ($cashback) {
            $cashback->companyCustomer->subtractCashback($cashback->amount, true);
            $cashback->softDelete();
            $cashback->save();

            $cashback->companyCustomer->save(false);
        }
    }

    /**
     * @param Order $order
     *
     * @throws \yii\base\InvalidConfigException
     */
    private function revertDeposit(Order $order)
    {
        if ($order->getPaymentExcess() > 0) {
            $this->cashflowService->withdrawFromDeposit(
                $order->company_cash_id,
                $order->division->id,
                $order->division->company_id,
                $order->company_customer_id,
                $order->getPaymentExcess(),
                \Yii::$app->user->id,
                $order
            );
        }
    }

    /**
     * @param Order $order
     */
    private function revertCashflow(Order $order)
    {
        $orderPrice = array_reduce($order->orderPayments, function (int $sum, OrderPayment $orderPayment) {
            if (!$orderPayment->payment->isAccountable()) {
                return $sum;
            }
            return $sum + $orderPayment->amount;
        }, 0);

        $cashflowData = new CashflowForm(\Yii::$app->user->id, [
            'date'        => $order->datetime,
            'cash_id'     => $order->company_cash_id,
            'comment'     => "Возрат записи №{$order->number}",
            'customer_id' => $order->company_customer_id,
            'division_id' => $order->division_id,
            'staff_id'    => $order->staff_id,
            'value'       => $orderPrice - max(0, $order->getPaymentExcess())
        ]);

        $payments = $this->getPayments($order->orderPayments);

        $this->cashflowService->refund($cashflowData, $order, $payments);
    }

    /**
     * @param OrderPayment[] $orderPayments
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
     * @param Order $order
     */
    private function revertWriteOff(Order $order)
    {
        if ($order->usage) {
            $this->usageService->delete($order->usage->id);
//            $order->unlink('usage', $order->usage);
        }
    }
}
