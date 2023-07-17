<?php

namespace core\services;

use core\models\finance\Payroll;
use core\models\finance\PayrollService as Service;
use core\models\finance\PayrollStaff;
use core\repositories\BaseRepository;
use core\repositories\PayrollRepository;
use core\services\dto\PayrollData;

class PayrollService
{
    private $baseRepository;
    private $payrollRepository;
    private $transactionManager;

    /**
     * PayrollService constructor.
     * @param BaseRepository $baseRepository
     * @param PayrollRepository $payrollRepository
     * @param TransactionManager $transactionManager
     */
    public function __construct(
        BaseRepository $baseRepository,
        PayrollRepository $payrollRepository,
        TransactionManager $transactionManager
    )
    {
        $this->baseRepository = $baseRepository;
        $this->payrollRepository = $payrollRepository;
        $this->transactionManager = $transactionManager;
    }

    /**
     * @param PayrollData $payrollData
     * @param $services
     * @param $staffs
     * @return Payroll
     * @throws \Exception
     */
    public function add(PayrollData $payrollData, $services, $staffs)
    {
        $payroll = Payroll::add(
            $payrollData->company_id,
            $payrollData->is_count_discount,
            $payrollData->name,
            $payrollData->salary,
            $payrollData->salary_mode,
            $payrollData->service_mode,
            $payrollData->service_value
        );

        $this->transactionManager->execute(function () use ($payroll, $services, $staffs) {
            $this->payrollRepository->add($payroll);
            foreach ((array)$services as $service) {
                $service->scheme_id = $payroll->id;
                $this->baseRepository->add($service);
            }
            foreach ((array)$staffs as $staff) {
                $staff->payroll_id = $payroll->id;
                $this->baseRepository->add($staff);
            }
        });

        return $payroll;
    }

    /**
     * @param $id
     * @param PayrollData $payrollData
     * @param $services
     * @param $staffs
     * @param $deletedServiceIDs
     * @param $deletedStaffIDs
     * @return Payroll
     * @throws \Exception
     */
    public function edit($id, PayrollData $payrollData, $services, $staffs,
                         $deletedServiceIDs, $deletedStaffIDs)
    {
        $payroll = $this->payrollRepository->find($id);

        $payroll->edit(
            $payrollData->company_id,
            $payrollData->is_count_discount,
            $payrollData->name,
            $payrollData->salary,
            $payrollData->salary_mode,
            $payrollData->service_mode,
            $payrollData->service_value
        );

        $this->transactionManager->execute(function () use (
            $payroll, $services, $staffs,
            $deletedServiceIDs, $deletedStaffIDs
        ) {
            $this->payrollRepository->edit($payroll);
            if (!empty($deletedServiceIDs)) {
                Service::deleteAll(['id' => $deletedServiceIDs]);
            }
            if (!empty($deletedStaffIDs)) {
                PayrollStaff::deleteAll(['id' => $deletedStaffIDs]);
            }
            foreach ((array)$services as $service) {
                $service->scheme_id = $payroll->id;
                if ($service->isNewRecord) {
                    $this->baseRepository->add($service);
                } else {
                    $this->baseRepository->edit($service);
                }
            }
            foreach ((array)$staffs as $staff) {
                $staff->payroll_id = $payroll->id;
                if ($staff->isNewRecord) {
                    $this->baseRepository->add($staff);
                } else {
                    $this->baseRepository->edit($staff);
                }
            }
        });

        return $payroll;
    }

    /**
     * @param $id
     * @throws \Exception
     */
    public function delete($id)
    {
        $payroll = $this->payrollRepository->find($id);
        $this->transactionManager->execute(function () use ($payroll) {
            $this->payrollRepository->delete($payroll);
        });
    }
}
