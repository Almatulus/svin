<?php

namespace services\notification;

use core\helpers\order\OrderConstants;
use core\helpers\order\OrderNotifier;
use core\models\company\Company;
use core\models\CompanyPaymentLog;
use core\models\customer\CompanyCustomer;
use core\models\order\Order;
use core\services\notification\ClientSMSNotificationService;

class ClientSMSNotificationServiceTest extends \Codeception\Test\Unit
{
    /**
     * @var ClientSMSNotificationService
     */
    private $service;

    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * @throws \yii\base\InvalidConfigException
     */
    protected function _before()
    {
        $this->service = \Yii::createObject(ClientSMSNotificationService::class);
        $this->make(OrderNotifier::class, [
            'sendSMSNotification' => function(){}
        ]);
    }

    protected function _after()
    {
    }

    // tests
    /**
     * @throws \Exception
     */
    public function testNotify()
    {
        $company = $this->tester->getFactory()->create(Company::class);
        $this->tester->getFactory()->create(CompanyPaymentLog::class, [
            'company_id' => $company->id
        ]);

        $companyCustomer = $this->tester->getFactory()
            ->create(CompanyCustomer::class, [
                'company_id' => $company->id,
            ]);
        $notify_true = $this->tester->getFactory()->seed(3, Order::class, [
            'notify_status'       => OrderConstants::NOTIFY_TRUE,
            'hours_before'        => 12,
            'datetime'            => date('Y-m-d H:i:s', time() + 6 * 60 * 60),
            'status'              => OrderConstants::STATUS_ENABLED,
            'company_customer_id' => $companyCustomer->id,
        ]);
        $notify_false = $this->tester->getFactory()->seed(3, Order::class, [
            'notify_status'       => OrderConstants::NOTIFY_FALSE,
            'hours_before'        => 0,
            'datetime'            => date('Y-m-d H:i:s', time() + 6 * 60 * 60),
            'status'              => OrderConstants::STATUS_ENABLED,
            'company_customer_id' => $companyCustomer->id,
        ]);

        verify($this->service->sendFutureVisitNotifications())->equals(count($notify_true));

        foreach ($notify_true as $order) {
            $this->tester->canSeeRecord(Order::class, [
                'id'            => $order->id,
                'notify_status' => OrderConstants::NOTIFY_DONE,
            ]);
        }
        foreach ($notify_false as $order) {
            $this->tester->canSeeRecord(Order::class, [
                'id'            => $order->id,
                'notify_status' => OrderConstants::NOTIFY_FALSE,
            ]);
        }
    }
}