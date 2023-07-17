<?php

namespace modules\order\services;

use core\models\customer\Customer;
use core\models\division\Division;
use core\models\division\DivisionPayment;
use core\models\division\DivisionService;
use core\models\order\Order;
use core\models\order\OrderService;
use core\models\Staff;
use core\services\dto\CustomerData;
use core\services\order\dto\OrderData;
use core\services\order\dto\OrderServiceData;
use core\services\order\OrderApiService;

class OrderApiServiceTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    /** @var OrderApiService */
    protected $service;

    public function testCreate()
    {
        $division = $this->tester->getFactory()->create(Division::class);
        $this->tester->getFactory()->create(DivisionPayment::class, ['division_id' => $division->id]);
        $staff = $this->tester->getFactory()->create(Staff::class);
        $staff->link('divisions', $division);

        $service = $this->tester->getFactory()->create(DivisionService::class);
        $service->link('divisions', $division);

        $datetime = new \DateTime();
        $note = $this->tester->getFaker()->text(20);
        $orderData = new OrderData($datetime, $division->id, $staff->id, $note, 0,
            null, null, null, $division->company_id, null, null);

        $orderServices = [new OrderServiceData($service->id, null, null, null, 1)];

        $customerData = new CustomerData(null,
            $this->tester->getFaker()->firstName,
            null,
            null,
            $this->tester->getFaker()->regexify("\+7 \d{3} \d{3} \d{2} \d{2}"),
            null);

        $order = $this->service->create($orderData, $orderServices, $customerData);

        $this->tester->canSeeRecord(Customer::class, [
            'name'  => $customerData->name,
            'phone' => $customerData->phone
        ]);

        $this->tester->canSeeRecord(Order::class, [
            'datetime'        => $datetime->format("Y-m-d H:i:s"),
            'division_id'     => $division->id,
            'staff_id'        => $staff->id,
            'company_cash_id' => $division->companyCash->id,
            'note'            => $note,
            'price'           => $service->price,
        ]);

        $this->tester->canSeeRecord(OrderService::class, [
            'order_id'            => $order->id,
            'division_service_id' => $service->id,
            'price'               => $service->price,
            'quantity'            => 1,
            'discount'            => 0
        ]);
    }

    protected function _before()
    {
        $this->service = \Yii::createObject(OrderApiService::class);
    }

    // tests

    protected function _after()
    {
    }
}