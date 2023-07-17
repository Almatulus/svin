<?php


namespace core\services;

use core\models\finance\CompanyCashflow;
use core\models\finance\CompanyCostItem;
use core\models\finance\PayrollStaff;
use core\models\order\OrderService;
use core\models\StaffPayment;
use core\models\StaffPaymentService;
use core\repositories\company\CompanyRepository;
use core\repositories\CompanyCashflowRepository;
use core\repositories\CompanyCostItemRepository;
use core\repositories\division\DivisionRepository;
use core\repositories\finance\StaffPaymentRepository;
use core\repositories\order\OrderRepository;
use core\repositories\order\OrderServiceRepository;
use core\repositories\StaffRepository;
use core\services\dto\SalaryServiceData;

class SalaryService
{
    /** @var StaffRepository */
    private $staffs;

    /** @var DivisionRepository */
    private $divisions;

    /** @var CompanyCashflowRepository */
    private $cashflows;

    /** @var OrderRepository */
    private $orders;

    /** @var OrderServiceRepository */
    private $orderServices;

    /** @var StaffPaymentRepository */
    private $staffPayments;

    /** @var TransactionManager */
    private $transactionManager;
    /**
     * @var CompanyRepository
     */
    private $companies;
    /**
     * @var CompanyCostItemRepository
     */
    private $costItems;

    /**
     * SalaryService constructor.
     *
     * @param StaffRepository           $staffs
     * @param DivisionRepository        $divisions
     * @param CompanyCashflowRepository $cashflows
     * @param OrderRepository           $orders
     * @param OrderServiceRepository    $orderServices
     * @param StaffPaymentRepository    $staffPayments
     * @param CompanyRepository         $companies
     * @param CompanyCostItemRepository $costItems
     * @param TransactionManager        $transactionManager
     */
    public function __construct(
        StaffRepository $staffs,
        DivisionRepository $divisions,
        CompanyCashflowRepository $cashflows,
        OrderRepository $orders,
        OrderServiceRepository $orderServices,
        StaffPaymentRepository $staffPayments,
        CompanyRepository $companies,
        CompanyCostItemRepository $costItems,
        TransactionManager $transactionManager
    ) {
        $this->staffs = $staffs;
        $this->divisions = $divisions;
        $this->cashflows = $cashflows;
        $this->orders = $orders;
        $this->orderServices = $orderServices;
        $this->staffPayments = $staffPayments;
        $this->transactionManager = $transactionManager;
        $this->companies = $companies;
        $this->costItems = $costItems;
    }

    /**
     * @param \DateTime           $start
     * @param \DateTime           $end
     * @param \DateTime           $paymentDate
     * @param int                 $salary
     * @param int                 $staff_id
     * @param int                 $division_id
     * @param int                 $user_id
     * @param SalaryServiceData[] $services
     *
     * @return StaffPayment
     * @throws \Exception
     */
    public function add(
        \DateTime $start,
        \DateTime $end,
        \DateTime $paymentDate,
        int $salary,
        int $staff_id,
        int $division_id,
        int $user_id,
        array $services
    ) {
        $staff = $this->staffs->find($staff_id);
        $division = $this->divisions->find($division_id);
        $schemes = $this->fetchSchemes($staff_id, $start, $end);

        $staffPayment = StaffPayment::create(
            $start->format("Y-m-d"),
            $end->format("Y-m-d"),
            $paymentDate->format("Y-m-d"),
            $salary,
            $staff->id
        );

        $services = $this->filterSalaryServices($services, $schemes);

        $cashflow = CompanyCashflow::addSalary(
            $paymentDate->format("Y-m-d H:i:s"),
            $division->id,
            $staffPayment->staff_id,
            $staffPayment->salary,
            $division->company_id,
            $user_id
        );

        $this->transactionManager->execute(function () use ($staffPayment, $cashflow, $services) {
            $this->staffPayments->add($staffPayment);

            if (!empty($services)) {
                $this->orders->setPaidByOrderServiceIds(array_map(function (StaffPaymentService $service) {
                    return $service->order_service_id;
                }, $services));

                foreach ($services as $service) {
                    $service->staff_payment_id = $staffPayment->id;
                    $this->staffPayments->add($service);
                }
            }

            $this->cashflows->add($cashflow);
            $this->staffPayments->linkWithCashflow($staffPayment, $cashflow);
        });

        return $staffPayment;
    }

    /**
     * @param int $staff_id
     * @param \DateTime $start
     * @param \DateTime $end
     * @return PayrollStaff[]
     */
    public function fetchSchemes(int $staff_id, \DateTime $start, \DateTime $end)
    {
        $schemes = PayrollStaff::find()->where([
            'AND',
            ['staff_id' => $staff_id],
            ['>=', 'started_time', $start->format("Y-m-d")]
        ])->all();

        if (empty($schemes)) {
            $schemes = PayrollStaff::find()
                ->where(['staff_id' => $staff_id])
                ->orderBy('started_time ASC')
                ->limit(1)
                ->all();
        }

        return $schemes;
    }

    /**
     * @param SalaryServiceData[] $services
     * @param array $schemes
     * @return array
     */
    private function filterSalaryServices(array $services, array $schemes)
    {
        return array_map(function (int $order_service_id, SalaryServiceData $serviceData) use ($schemes) {
            $staffPaymentService = new StaffPaymentService([
                'order_service_id' => $serviceData->getOrderServiceId(),
                'percent'          => $serviceData->getPercent(),
                'sum'              => $serviceData->getSum()
            ]);
            $scheme = $this->getScheme($schemes, $staffPaymentService->orderService);
            $staffPaymentService->payroll_id = $scheme->payroll_id;
            return $staffPaymentService;

        }, array_keys($services), $services);
    }

    /**
     * @param PayrollStaff[] $schemes
     * @param OrderService $service
     * @return PayrollStaff
     */
    private function getScheme(array $schemes, OrderService $service)
    {
        $order_time = strtotime($service->order->datetime);

        $selected_scheme = current($schemes);
        foreach ($schemes as $scheme) {
            $selected_scheme_time = strtotime($scheme->started_time);
            $scheme_time = strtotime($scheme->started_time);
            if ($order_time >= $scheme_time && $scheme_time > $selected_scheme_time) {
                $selected_scheme = $scheme;
            }
        }
        return $selected_scheme;
    }

    /**
     * @param int $id
     */
    public function delete(int $id)
    {
        $staffPayment = $this->staffPayments->find($id);

        $this->transactionManager->execute(function () use ($staffPayment) {
            $orderServiceIds = array_map(function (StaffPaymentService $service) {
                return $service->order_service_id;
            }, $staffPayment->services);
            $this->orders->setPaidByOrderServiceIds($orderServiceIds, false);
            $this->staffPayments->deleteServices($staffPayment);

            $cashflow = $staffPayment->cashflow;
            if ($cashflow) {
                $this->staffPayments->unlinkWithCashflow($staffPayment);
                $this->cashflows->delete($cashflow);
            }

            $this->staffPayments->delete($staffPayment);
        });
    }

    /**
     * @param int $id
     * @param int $company_id
     *
     * @throws \Exception
     */
    public function clear(int $id, int $company_id)
    {
        $staffPayment = $this->staffPayments->find($id);
        $costItem = $this->costItems->findSalaryPaymentCostItemByCompany($company_id);
        $cashflow = $this->cashflows->findStaffSalaryPaymentForDate(
            $staffPayment->staff_id,
            $costItem->id,
            $staffPayment->salary,
            $staffPayment->created_at
        );

        $this->transactionManager->execute(function () use ($staffPayment, $cashflow) {
            $this->orders->setStaffUnpaidForPeriod(
                $staffPayment->staff_id,
                new \DateTime($staffPayment->end_date),
                new \DateTime($staffPayment->start_date)
            );

            $this->cashflows->delete($cashflow);

            $this->staffPayments->delete($staffPayment);
        });
    }

    /**
     * @param int $staff_id
     * @param int $division_id
     * @param \DateTime $start
     * @param \DateTime $end
     * @return OrderService[]
     */
    public function fetchServices(int $staff_id, int $division_id, \DateTime $start, \DateTime $end)
    {
        $staff = $this->staffs->find($staff_id);
        $division = $this->divisions->find($division_id);

        $services = $this->orderServices->findByStaffAndRange(
            $staff->id,
            $division->id,
            $start,
            $end,
            true,
            false
        );

        $schemes = $this->fetchSchemes($staff_id, $start, $end);

        if (empty($schemes)) {
            throw new \DomainException("У сотрудника нет схемы расчета ЗП.");
        }

        return array_map(function (OrderService $orderService) use ($schemes) {
            $scheme = $this->getScheme($schemes, $orderService);
            $orderService->setPayroll($scheme->payroll);
            return $orderService;
        }, $services);
    }
}