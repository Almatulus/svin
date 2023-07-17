<?php

namespace modules\finance\services;

use core\helpers\order\OrderConstants;
use core\models\division\Division;
use core\models\finance\CompanyCashflow;
use core\models\finance\Payroll;
use core\models\finance\PayrollStaff;
use core\models\order\Order;
use core\models\order\OrderService;
use core\models\Staff;
use core\models\StaffPayment;
use core\models\StaffPaymentService;
use core\models\user\User;
use core\services\dto\SalaryServiceData;
use core\services\SalaryService;

class SalaryServiceTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    /** @var SalaryService $service */
    protected $service;

    /**
     * @throws \yii\base\InvalidConfigException
     * @throws \Exception
     */
    public function testAddWihEmptyServices()
    {
        $end = new \DateTime();
        $start = (clone $end)->modify("-1 month");
        $salary = $this->tester->getFaker()->randomNumber(5);

        $staff = $this->tester->getFactory()->create(Staff::class);
        $division = $this->tester->getFactory()->create(Division::class);
        $user = $this->tester->getFactory()->create(User::class, ['company_id' => $division->company_id]);

        // for blameable behaviour
        \Yii::$app->set('user', $user);

        $payroll = $this->tester->getFactory()->create(Payroll::class, [
            'service_value' => 20,
            'service_mode'  => Payroll::PAYROLL_MODE_PERCENTAGE
        ]);
        $this->tester->getFactory()->create(PayrollStaff::class, [
            'payroll_id' => $payroll->id,
            'staff_id'   => $staff->id
        ]);

        $model = $this->service->add($start, $end, $start, $salary, $staff->id, $division->id, $user->id, []);

        $this->tester->canSeeRecord(StaffPayment::class, [
            'start_date' => $start->format("Y-m-d"),
            'end_date'   => $end->format("Y-m-d"),
            'staff_id'   => $staff->id,
        ]);

        $this->tester->canSeeRecord(CompanyCashflow::class, [
            'value'         => $salary,
            'staff_id'      => $staff->id,
            'receiver_mode' => CompanyCashflow::RECEIVER_STAFF
        ]);

        $this->tester->cantSeeRecord(StaffPaymentService::class, [
            'staff_payment_id' => $model->id
        ]);
    }

    /**
     * @throws \yii\base\InvalidConfigException
     * @throws \Exception
     */
    public function testAddWithServices()
    {
        $end = new \DateTime();
        $start = (clone $end)->modify("-1 month");
        $salary = $this->tester->getFaker()->randomNumber(5);
        $percent = $this->tester->getFaker()->numberBetween(0, 100);

        $staff = $this->tester->getFactory()->create(Staff::class);
        $division = $this->tester->getFactory()->create(Division::class);
        $user = $this->tester->getFactory()->create(User::class, ['company_id' => $division->company_id]);

        // for blameable behaviour
        \Yii::$app->set('user', $user);

        $payroll = $this->tester->getFactory()->create(Payroll::class, [
            'service_value' => $percent,
            'service_mode'  => Payroll::PAYROLL_MODE_PERCENTAGE
        ]);
        $this->tester->getFactory()->create(PayrollStaff::class, [
            'payroll_id' => $payroll->id,
            'staff_id'   => $staff->id
        ]);

        $orderServices = [];
        $orders = $this->tester->getFactory()->seed(2, Order::class, [
            'staff_id'    => $staff->id,
            'division_id' => $division->id
        ]);
        foreach ($orders as $order) {
            $orderServices = array_merge($this->tester->getFactory()->seed(2, OrderService::class, [
                'order_id' => $order->id
            ]), $orderServices);
        }

        $servicesData = array_map(function (OrderService $orderService) use ($percent) {
            $price = intval($orderService->price * (100 - $percent));
            return new SalaryServiceData($orderService->id, $percent, $price);
        }, $orderServices);

        // test adding salary with empty services
        $model = $this->service->add($start, $end, $start, $salary, $staff->id, $division->id, $user->id, $servicesData);

        $this->tester->canSeeRecord(StaffPayment::class, [
            'start_date' => $start->format("Y-m-d"),
            'end_date'   => $end->format("Y-m-d"),
            'staff_id'   => $staff->id
        ]);

        $this->tester->canSeeRecord(CompanyCashflow::class, [
            'value'         => $salary,
            'staff_id'      => $staff->id,
            'receiver_mode' => CompanyCashflow::RECEIVER_STAFF
        ]);

        foreach ($servicesData as $servicesDatum) {
            /** @var SalaryServiceData $servicesDatum */
            $this->tester->canSeeRecord(StaffPaymentService::class, [
                'order_service_id' => $servicesDatum->getOrderServiceId(),
                'staff_payment_id' => $model->id,
                'payroll_id'       => $payroll->id,
                'percent'          => $servicesDatum->getPercent(),
                'sum'              => $servicesDatum->getSum()
            ]);
        }
    }

    public function testDelete()
    {
        $user = $this->tester->getFactory()->create(User::class);

        // for blameable behaviour
        \Yii::$app->set('user', $user);

        $staffPayment = $this->tester->getFactory()->create(StaffPayment::class);
        $services = $this->tester->getFactory()->seed(3, StaffPaymentService::class, [
            'staff_payment_id' => $staffPayment->id
        ]);
        $cashflow = $this->tester->getFactory()->create(CompanyCashflow::class,
            [
                'user_id'    => $user->id,
                'created_by' => $user->id,
                'updated_by' => $user->id,
                'company_id' => $user->company_id,
            ]
        );
        $staffPayment->link('cashflow', $cashflow);

        $this->service->delete($staffPayment->id);

        $this->tester->cantSeeRecord(StaffPayment::class, ['id' => $staffPayment->id]);
        foreach ($services as $service) {
            $this->tester->cantSeeRecord(StaffPaymentService::class, [
                'order_service_id' => $service->order_service_id,
                'staff_payment_id' => $service->staff_payment_id
            ]);
        }
        $this->tester->cantSeeRecord(CompanyCashflow::class, ['id' => $cashflow->id]);
    }

    public function testFetchServices()
    {
        $end = new \DateTime();
        $start = (clone $end)->modify("-1 month");
        $salary = $this->tester->getFaker()->randomNumber(5);
        $percent = $this->tester->getFaker()->numberBetween(0, 100);

        $staff = $this->tester->getFactory()->create(Staff::class);
        $division = $this->tester->getFactory()->create(Division::class);
        $payroll = $this->tester->getFactory()->create(Payroll::class, [
            'service_value' => $percent,
            'service_mode'  => Payroll::PAYROLL_MODE_PERCENTAGE
        ]);
        $this->tester->getFactory()->create(PayrollStaff::class, [
            'payroll_id' => $payroll->id,
            'staff_id'   => $staff->id
        ]);

        $orderServices = [];
        $orders = $this->tester->getFactory()->seed(2, Order::class, [
            'staff_id'    => $staff->id,
            'division_id' => $division->id,
            'is_paid'     => false,
            'status'      => OrderConstants::STATUS_FINISHED
        ]);
        // create one paid order, to be sure that paid orders are not fetched
        $orders[] = $this->tester->getFactory()->create(Order::class, [
            'staff_id'    => $staff->id,
            'division_id' => $division->id,
            'is_paid'     => true,
            'status'      => OrderConstants::STATUS_FINISHED
        ]);
        foreach ($orders as $order) {
            $orderServices = array_merge($this->tester->getFactory()->seed(2, OrderService::class, [
                'order_id' => $order->id
            ]), $orderServices);
        }

        $services = $this->service->fetchServices($staff->id, $division->id, $start, $end);

        verify(sizeof($services))->equals(sizeof($orderServices) - 2);
    }

    public function testFetchServicesOfStaffWithoutPayroll()
    {
        $this->expectException(\DomainException::class);

        $end = new \DateTime();
        $start = (clone $end)->modify("-1 month");

        $staff = $this->tester->getFactory()->create(Staff::class);
        $division = $this->tester->getFactory()->create(Division::class);

        $services = $this->service->fetchServices($staff->id, $division->id, $start, $end);
    }

    protected function _before()
    {
        /** @var SalaryService service */
        $this->service = \Yii::createObject(SalaryService::class);
    }

    protected function _after()
    {
    }
}