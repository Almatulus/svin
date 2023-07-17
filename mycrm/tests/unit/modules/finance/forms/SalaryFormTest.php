<?php
namespace modules\finance\forms;

use app\tests\fixtures\CompanyCashflowFixture;
use app\tests\fixtures\OrderServiceFixture;
use app\tests\fixtures\PayrollFixture;
use app\tests\fixtures\PayrollStaffFixture;
use app\tests\fixtures\UserFixture;
use core\forms\finance\SalaryForm;
use yii\helpers\ArrayHelper;

class SalaryFormTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * @var SalaryForm
     */
    private $_form;

    protected function _before()
    {
        $this->markTestSkipped();
        $this->tester->haveFixtures([
            'cashflow' => CompanyCashflowFixture::className(),
            'order_service' => OrderServiceFixture::className(),
            'payroll' => PayrollFixture::className(),
            'payroll_staff' => PayrollStaffFixture::className(),
            'user' => UserFixture::className()
        ]);

        $this->tester->login();

        $this->_form = new SalaryForm();
    }

    // tests
    public function testGetServices()
    {
        $services = $this->_form->getServices();

        $ids = ArrayHelper::getColumn($services, "id");
        expect("Services id match", $ids)->equals([
            $this->tester->grabFixture("order_service")->data["order_service_4"]["id"],
            $this->tester->grabFixture("order_service")->data["order_service_5"]["id"],
        ]);

        $payroll = $this->tester->grabFixture("payroll", "payroll_without_discount");
        $paymentAmounts = ArrayHelper::getColumn($services, "payment_amount");
        expect("Services payment match", $paymentAmounts)->equals([
            $payroll->calcServicePayment($this->tester->grabFixture("order_service", "order_service_4")),
            $payroll->calcServicePayment($this->tester->grabFixture("order_service", "order_service_5"))
        ]);
    }

    public function testGetSchemes()
    {
        $schemes = $this->_form->getSchemes();
        $ids = ArrayHelper::getColumn($schemes, "payroll_id");
        expect("Payrolls id match", $ids)->equals([
            $this->tester->grabFixture("payroll_staff", "payroll_staff_1")->payroll_id,
        ]);
    }

    public function testCalcSalaryPeriods()
    {
        $payments = ArrayHelper::getColumn($this->_form->calcSalaryPeriods(), "payment");
        $payroll = $this->tester->grabFixture("payroll", "payroll_without_discount");

        $periodPayment = $this->_form->getMonths(
            $this->_form->payment_from,
            (new \DateTime($this->_form->payment_till))->modify("+ 1 day")->format("Y-m-d")
        );
        expect("Salary periods match", $payments)->equals([
            $periodPayment * $payroll->salary
        ]);
    }
}