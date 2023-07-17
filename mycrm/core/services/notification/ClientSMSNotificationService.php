<?php

namespace core\services\notification;

use core\repositories\order\OrderRepository;
use core\services\TransactionManager;
use core\models\customer\CustomerRequestTemplate;
use core\helpers\order\OrderNotifier;
use core\models\order\Order;
use Yii;

class ClientSMSNotificationService
{
    private $transactionManager;
    private $orders;

    /**
     * @param OrderRepository    $orders
     * @param TransactionManager $transactionManager
     */
    public function __construct(
        OrderRepository $orders,
        TransactionManager $transactionManager
    ) {
        $this->transactionManager = $transactionManager;
        $this->orders             = $orders;
    }

    /**
     * @throws \Exception
     */
    public function sendFutureVisitNotifications()
    {
        echo "------- notification sending stated -------\n\n";

        $orders = $this->orders->findAllUnNotified();

        $customers_count = 0;
        foreach ($orders as $order) {
            /* @var Order $order */
            if ( ! $order->needsNotification()) {
                continue;
            }

            $company  = $order->companyCustomer->company;
            if ( ! $company->hasEnoughBalance(Yii::$app->params['sms_cost'])) {
                echo "'{$company->name}' has not enough balance to send message\n";
                continue;
            }

            $order->setNotified();

            $customer = $order->companyCustomer->customer;
            $this->transactionManager->execute(function () use (
                $order,
                $customer,
                $company,
                $customers_count
            ) {
                (new OrderNotifier())->sendSMSNotification(
                    $order,
                    $customer->phone,
                    CustomerRequestTemplate::TYPE_VISIT_REMIND
                );
                $this->orders->save($order);
            });

            $customers_count++;
            echo "'{$company->name}': {$customers_count}) '{$customer->phone}' received notification message (time - {$order->datetime})\n";
        }

        echo "!!! {$customers_count} customers were notified !!! \n\n";
        echo "------- notification sending finished -------\n";

        return $customers_count;
    }
}
