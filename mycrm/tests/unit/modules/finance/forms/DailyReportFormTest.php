<?php
namespace modules\finance\forms;

use app\tests\fixtures\CompanyCashflowFixture;
use app\tests\fixtures\CompanyCashflowServiceFixture;
use app\tests\fixtures\CompanyFixture;
use app\tests\fixtures\UserFixture;
use core\forms\finance\DailyReportForm;
use yii\helpers\ArrayHelper;

class DailyReportFormTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * @var DailyReportForm
     */
    private $_form;

    protected function _before()
    {
        $this->markTestSkipped();
        $this->tester->haveFixtures([
            'cashflow' => CompanyCashflowFixture::className(),
            'cashflow-service' => CompanyCashflowServiceFixture::className(),
            'company' => CompanyFixture::className(),
            'user' => UserFixture::className()
        ]);

        $this->tester->login();

        $this->_form = new DailyReportForm();
    }

    public function testGetCashflows()
    {
        $cashflows = $this->_form->getCashflows();
        $ids = ArrayHelper::getColumn($cashflows, "id");

        expect("Matching cashflows id", $ids)->equals([
            $this->tester->grabFixture('cashflow')->data['company_cashflow_3']['id']
        ]);
    }

    public function testGetPreviousDayBalance()
    {
        $previousDayBalance = $this->_form->getPreviousDayBalance();

        expect("Paid amount match", $previousDayBalance['paid'])->equals(
            $this->tester->grabFixture('cashflow')->data['company_cashflow_5']['value']
        );
        expect("Discount match", $previousDayBalance['discount'])->equals(
            $this->tester->grabFixture('cashflow-service', 'cashflow_service_1')->orderService->discount
        );
    }
}