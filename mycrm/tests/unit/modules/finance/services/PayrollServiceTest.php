<?php

namespace app\tests\unit\modules\finance\services;

use core\models\company\Company;
use core\models\division\DivisionService;
use core\models\finance\Payroll;
use core\models\finance\PayrollService as Service;
use core\models\finance\PayrollStaff as Staff;
use core\services\dto\PayrollData;
use core\services\PayrollService;

class PayrollServiceTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * @var PayrollService
     */
    private $payrollService;

    /**
     * @var Company
     */
    private $company;

    protected function _before()
    {
        $this->payrollService = \Yii::createObject(PayrollService::class);

        if (!$this->company) {
            $this->company = $this->tester->getFactory()->create(Company::class);
        }
    }

    public function testAdd()
    {
        $divisionService = $this->tester->getFactory()->create(DivisionService::class);
        $staff = $this->tester->getFactory()->create(\core\models\Staff::class);

        $payrollData = new PayrollData(
            $this->company->id,
            false,
            "Payroll Name",
            30000,
            1,
            2,
            15
        );
        $service = new Service([
            'division_service_id' => $divisionService->id,
            'service_value'       => 40,
            'service_mode'        => 1,
        ]);
        $staff = new Staff([
            'staff_id'     => $staff->id,
            'started_time' => date("Y-m-d H:i:s")
        ]);

        $payroll = $this->payrollService->add($payrollData, [$service], [$staff]);

        expect("Payroll Model", $payroll)->isInstanceOf(Payroll::class);
        expect("Payroll Model id is not empty", $payroll->id)->notNull();
        $this->tester->canSeeRecord(Service::className(), ['scheme_id' => $payroll->id]);
        $this->tester->canSeeRecord(Staff::className(), ['payroll_id' => $payroll->id]);
    }

    public function testEdit()
    {
        $payroll = $this->tester->getFactory()->create(Payroll::class);

        $payrollData = new PayrollData(
            $this->company->id,
            true,
            "New Payroll Name",
            50000,
            1,
            2,
            15
        );

        $payroll = $this->payrollService->edit(
            $payroll->id,
            $payrollData,
            null,
            null,
            [],
            []
        );

        expect("Payroll Model", $payroll)->isInstanceOf(Payroll::class);
        expect("Edited name", $payroll->name)->equals("New Payroll Name");
        expect("Edited salary", $payroll->salary)->equals(50000);
        expect("Edited is_count_discount ", $payroll->is_count_discount)->equals(true);
    }

    public function testDelete()
    {
        $payroll = $this->tester->getFactory()->create(Payroll::class);

        $this->payrollService->delete($payroll->id);
        $this->tester->cantSeeRecord(Payroll::className(), ['id' => $payroll->id]);
    }
}