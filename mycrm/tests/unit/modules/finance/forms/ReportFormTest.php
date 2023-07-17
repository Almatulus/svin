<?php
namespace modules\finance\forms;

use app\tests\fixtures\{
    CompanyCashflowFixture, CompanyCostItemFixture, DivisionCostItemFixture, UserFixture
};
use core\forms\finance\ReportForm;
use core\models\finance\CompanyCashflow;
use yii\helpers\ArrayHelper;

class ReportFormTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * @var ReportForm
     */
    private $_form;

    protected function _before()
    {
        $this->markTestSkipped();
        $this->tester->haveFixtures([
            'cashflow' => CompanyCashflowFixture::className(),
            'division-cost-item' => DivisionCostItemFixture::className(),
            'costItem' => CompanyCostItemFixture::className(),
            'user' => UserFixture::className()
        ]);

        $this->tester->login();

        $this->_form = new ReportForm();
    }

    // tests
    public function testGetCashflows()
    {
        $cashflows = $this->_form->getCashflows();

        $expectedCashflows = CompanyCashflow::find()
            ->select(['date', 'Sum(value) as value', 'cost_item_id', 'type'])
            ->joinWith('costItem', false)
            ->company(\Yii::$app->user->identity->company_id)
            ->range(date("Y-m-d", strtotime("-1 months +1 day")), date("Y-m-d", strtotime("+1 day")))
            ->groupBy(['date', 'cost_item_id', 'type'])
            ->asArray()
            ->all();

        expect("Cashflows match", $cashflows)->equals($expectedCashflows);
    }

    public function testGetCashflowsData()
    {
        expect("Cashflows data not empty", $this->_form->getCashflowsData())->notNull();
    }

    public function testGetIncomeCostItems()
    {
        $ids = ArrayHelper::getColumn($this->_form->getIncomeCostItems(), "id");
        expect("Matching income cost items id", $ids)->equals([
            $this->tester->grabFixture('costItem')->data['company_cost_item_income']['id']
        ]);
    }

    public function testGetExpenseCostItems()
    {
        $ids = ArrayHelper::getColumn($this->_form->getExpenseCostItems(), "id");
        expect("Matching expense cost items id", $ids)->equals([
            $this->tester->grabFixture('costItem')->data['company_cost_item_outcome']['id'],
            $this->tester->grabFixture('costItem')->data['company_cost_item_order']['id'],
            $this->tester->grabFixture('costItem')->data['company_cost_item_salary']['id']
        ]);
    }

}